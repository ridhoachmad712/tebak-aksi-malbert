<?php

namespace Tests\Feature;

use App\Livewire\Guru\RekapKelas;
use App\Models\Kelas;
use App\Models\User;
use App\Services\Rekap;
use App\Services\RingkasanMonev;
use Database\Seeders\DataAwalSeeder;
use Database\Seeders\DataContohSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MonevKepsekTest extends TestCase
{
    use RefreshDatabase;

    public function test_kasus_peta_dan_rincian_mengikuti_periode_dengan_status_terkini(): void
    {
        $contoh = \App\Models\KasusPembinaan::firstOrFail();
        \App\Models\KasusPembinaan::query()->update(['tanggal' => '2026-08-15']);
        $kelas = $contoh->kelas;
        foreach ([['2026-09-01', null], ['2026-09-30', 'positif'], ['2026-08-31', null], ['2026-10-01', null]] as [$tanggal, $status]) {
            $kasus = $contoh->replicate();
            $kasus->tanggal = $tanggal;
            $kasus->status_akhir = $status;
            $kasus->save();
        }
        [$dari, $sampai] = Rekap::periodeBulan(2026, 9);
        $semua = Rekap::indikator($dari, $sampai);
        $this->assertSame(2, collect($semua['per_kelas'])->sum('kasus_total'));
        $this->assertSame(1, collect($semua['per_kelas'])->sum('kasus_aktif'));
        $this->assertSame(1, collect($semua['per_kelas'])->sum('kasus_selesai'));
        $r = Rekap::indikator($dari, $sampai, $kelas)['per_kelas'][0];
        $this->assertSame($r['kasus_total'], $r['kasus_aktif'] + $r['kasus_selesai']);
        Livewire::withQueryParams(['bulan' => '2026-09'])->test(\App\Livewire\MonevKelas::class, ['kelas' => $kelas])
            ->assertViewHas('kasusAktif', fn ($kasus) => $kasus->count() === 1 && $kasus->first()->tanggal->toDateString() === '2026-09-01');
        $this->get('/monev?bulan=2026-09')->assertOk()->assertSee('Total kasus')->assertSee('Masih dibina')->assertSee('Selesai');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([DataAwalSeeder::class, DataContohSeeder::class]);
        $this->actingAs(User::where('email', 'kepsek@tebakaksi.test')->first());
    }

    protected function indikatorContoh(array $ubah = []): array
    {
        $dasar = fn ($nilai, $target) => ['nilai' => $nilai, 'target' => $target, 'keterangan' => '-'];

        return array_replace([
            'murid_aktif' => $dasar(84.0, 80),
            'guru_aktif' => $dasar(100.0, 90),
            'budaya_membaca' => $dasar(75.0, 80),
            'kemampuan_literasi' => $dasar(53.6, 85),
            'keberhasilan_pembinaan' => $dasar(100.0, 70) + ['cukup' => false],
            'penurunan_pelanggaran' => $dasar(null, 20),
            'kelas_di_bawah_target' => collect(),
        ], $ubah);
    }

    public function test_aturan_warna_hanya_untuk_nilai_di_bawah_target(): void
    {
        $this->assertNull(RingkasanMonev::warna(85, 80));
        $this->assertNull(RingkasanMonev::warna(null, 80));
        $this->assertSame('amber', RingkasanMonev::warna(72, 80));
        $this->assertSame('rose', RingkasanMonev::warna(53.6, 85));
    }

    public function test_pilar_selisih_tren_dan_data_belum_cukup(): void
    {
        $lalu = $this->indikatorContoh(['budaya_membaca' => ['nilai' => 72.9, 'target' => 80, 'keterangan' => '-']]);
        $pilar = RingkasanMonev::pilar($this->indikatorContoh(), $lalu);

        $this->assertSame(['Budaya Membaca', 'Kemampuan Literasi', 'Karakter', 'Pelaksanaan'], array_keys($pilar));
        $budaya = collect($pilar['Budaya Membaca'])->keyBy('kunci');
        $this->assertSame('tercapai', $budaya['murid_aktif']['status']);
        $this->assertSame('kurang', $budaya['budaya_membaca']['status']);
        $this->assertSame(5.0, $budaya['budaya_membaca']['selisih']);
        $this->assertSame(2.1, $budaya['budaya_membaca']['tren']);

        $karakter = collect($pilar['Karakter'])->keyBy('kunci');
        $this->assertSame('belum_cukup', $karakter['keberhasilan_pembinaan']['status']);
        $this->assertSame('tanpa_data', $karakter['penurunan_pelanggaran']['status']);

        // Di tingkat kelas penurunan pelanggaran tidak ditampilkan
        $this->assertCount(1, RingkasanMonev::pilar($this->indikatorContoh(), null, true)['Karakter']);

        $kalimat = RingkasanMonev::kalimat($pilar, $this->indikatorContoh(), 'Oktober 2026', 'September');
        $this->assertSame('Oktober 2026: 2 dari 4 target tercapai.', $kalimat[0]);
        $this->assertStringContainsString('Dibanding September, rata-rata Budaya Membaca naik 2,1 poin', $kalimat[1]);
        $this->assertStringContainsString('rata-rata Kemampuan Literasi seluruh sekolah (53,6%, target 85%)', $kalimat[2]);
    }

    public function test_periode_pembanding_dan_daftar_bulan_indonesia(): void
    {
        $bulan = today()->format('Y-m');
        $p = Rekap::periode('bulan', $bulan, '1');
        $this->assertSame(Rekap::labelBulan(today()), $p['label']);
        $this->assertSame(['bulan' => $bulan], $p['unduh']);
        $this->assertNull(Rekap::periode('semester', $bulan, '1')['sebelumnya']);
        $this->assertSame('Semester Ganjil', Rekap::periode('semester', $bulan, '2')['sebelumnya']['label']);
        $this->assertSame(Rekap::labelBulan(today()), Rekap::daftarBulan()[$bulan]);
        $this->assertSame('81,9%', Rekap::persenTeks(81.9));
        $this->assertSame('100%', Rekap::persenTeks(100.0));
    }

    public function test_monev_menampilkan_ringkasan_pilar_dan_peta_kelas(): void
    {
        $kelas = Kelas::aktif()->first();

        $this->get('/monev')->assertOk()
            ->assertSee('Ringkasan')
            ->assertSee('target tercapai')
            ->assertSee('Capaian per pilar program')
            ->assertSee('Kemampuan Literasi')
            ->assertSee('Pelaksanaan')
            ->assertSee('Peta kelas')
            // Kepala sekolah melihat bulan bawaan: bulan lalu selama bulan berjalan belum selesai
            ->assertSee(route('monev.kelas', ['kelas' => $kelas->id, 'bulan' => Rekap::bulanBawaan()]), false)
            ->assertSee(Rekap::daftarBulan()[Rekap::bulanBawaan()])
            ->assertDontSee('type="month"', false);

        // Tautan lama dengan ?kelas= diarahkan ke halaman rincian kelas
        $this->get('/monev?kelas='.$kelas->id)->assertRedirect();
    }

    public function test_rincian_kelas_dan_cetak_ringkasan(): void
    {
        $kelas = Kelas::aktif()->first();

        $this->get('/monev/kelas/'.$kelas->id)->assertOk()
            ->assertSee('Kelas '.$kelas->nama)
            ->assertSee('Murid perlu perhatian')
            ->assertSee('Kasus dalam pembinaan')
            ->assertSee('Capaian kelas per pilar program')
            ->assertDontSee('Penurunan pelanggaran per semester');

        $this->get('/monev/cetak?bulan='.today()->format('Y-m'))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->get('/monev/cetak?periode=semester&semester=1')->assertOk();

        $this->actingAs(User::where('email', 'guru1@tebakaksi.test')->first());
        $this->get('/monev/kelas/'.$kelas->id)->assertForbidden();
        $this->get('/monev/cetak')->assertForbidden();
    }

    public function test_rekap_ringkasan_urutan_dan_saringan(): void
    {
        $kelas = Kelas::aktif()->first();
        $komponen = Livewire::withQueryParams(['kelas' => $kelas->id])->test(RekapKelas::class)
            ->assertSee('Rata-rata kelas')
            ->assertSee('murid perlu perhatian')
            ->assertSee('Hanya yang perlu perhatian')
            ->assertDontSee('type="month"', false);

        $komponen->set('urut', 'terendah');
        $nilai = $komponen->viewData('murid')->pluck('budaya_persen')->map(fn ($v) => $v ?? -1)->all();
        $urut = $nilai;
        sort($urut);
        $this->assertSame($urut, $nilai);

        $komponen->set('hanyaPerhatian', true);
        $this->assertTrue($komponen->viewData('murid')->every(fn ($b) => RekapKelas::perluPerhatian($b['budaya_persen'])));
        $this->assertSame($komponen->viewData('ringkasan')['perhatian'], $komponen->viewData('murid')->count());

        $komponen->set('laporan', 'pelanggaran')->assertSee('masih dibina');
    }

    public function test_dashboard_kepsek_dan_urutan_menu(): void
    {
        $this->get('/arah')->assertRedirect(route('kepsek.dasbor'));

        $halaman = $this->get('/dashboard')->assertOk()
            ->assertSee('Capaian 5 aspek inovasi')
            ->assertSee('Kepatuhan Perilaku')
            ->assertSee('Ekosistem Literat')
            ->assertSee('Perlu tindakan Bapak/Ibu')
            ->assertSee('Pencatatan kelas minggu ini')
            ->assertSee('Kelas yang paling butuh perhatian')
            ->assertSee('Kabar baik')
            ->assertSee('Cetak ringkasan Monev');

        // Menu kepala sekolah berurutan: Dashboard, Rekap, Monev
        $halaman->assertSeeInOrder(['href="'.route('kepsek.dasbor').'"', 'href="'.route('guru.rekap').'"', 'href="'.route('monev').'"'], false);
        $this->assertStringNotContainsString('Kegiatan Harian', $halaman->getContent());

        $this->actingAs(User::where('email', 'guru1@tebakaksi.test')->first());
        $this->get('/dashboard')->assertForbidden();
    }
}
