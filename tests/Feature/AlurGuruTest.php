<?php

namespace Tests\Feature;

use App\Livewire\Guru\Beranda;
use App\Livewire\Guru\KegiatanHarianForm;
use App\Livewire\Guru\PembinaanDetail;
use App\Livewire\Guru\PembinaanForm;
use App\Models\JenisPelanggaran;
use App\Models\KasusPembinaan;
use App\Models\KegiatanHarian;
use App\Models\Kelas;
use App\Models\Murid;
use App\Models\TahunAjaran;
use App\Models\TindakanPembinaan;
use App\Models\User;
use App\Services\Rekap;
use Database\Seeders\DataAwalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class AlurGuruTest extends TestCase
{
    use RefreshDatabase;

    protected User $guru;

    protected Kelas $kelas;

    protected function setUp(): void
    {
        parent::setUp();
        // Rabu, 14 Oktober 2026
        Carbon::setTestNow('2026-10-14 08:00:00');
        $this->seed(DataAwalSeeder::class);

        $ta = TahunAjaran::create(['nama' => '2026/2027', 'mulai' => '2026-07-13', 'selesai' => '2027-06-30', 'aktif' => true]);
        $this->guru = User::create(['name' => 'Guru Uji', 'email' => 'uji@tebakaksi.test', 'password' => 'password', 'peran' => 'guru', 'jenis_guru' => 'kelas', 'status' => 'aktif']);
        $this->kelas = Kelas::create(['tahun_ajaran_id' => $ta->id, 'tingkat' => 4, 'nama' => '4A', 'wali_kelas_id' => $this->guru->id]);
        foreach (['Ani', 'Budi', 'Citra'] as $nama) {
            $this->kelas->murid()->attach(Murid::create(['nama' => $nama])->id);
        }
    }

    protected function murid(string $nama): Murid
    {
        return Murid::where('nama', $nama)->first();
    }

    public function test_centang_tersimpan_dan_tidak_hadir_mengosongkan_indikator_lain(): void
    {
        $ani = $this->murid('Ani');
        $budi = $this->murid('Budi');

        Livewire::actingAs($this->guru)->test(KegiatanHarianForm::class, ['kelas' => $this->kelas->id])
            ->call('simpan', [
                $ani->id => ['hadir' => true, 'konsisten' => true, 'jurnal' => true, 'memahami' => false, 'merangkum' => false, 'menyampaikan' => false],
                // Data tidak konsisten dari klien: tidak hadir tetapi konsisten dicentang
                $budi->id => ['hadir' => false, 'konsisten' => true],
            ]);

        $kegiatan = KegiatanHarian::where('kelas_id', $this->kelas->id)->whereDate('tanggal', today())->firstOrFail();
        $this->assertTrue($kegiatan->catatanMurid()->where('murid_id', $ani->id)->value('konsisten'));
        $this->assertFalse((bool) $kegiatan->catatanMurid()->where('murid_id', $budi->id)->value('konsisten'));
        $this->assertSame('membuat', $kegiatan->riwayat()->value('aksi'));
        $this->assertSame($this->guru->id, $kegiatan->dibuat_oleh);
    }

    public function test_satu_catatan_bersama_per_kelas_per_hari_dan_jejak_guru_mapel(): void
    {
        $mapel = User::create(['name' => 'Guru PJOK', 'email' => 'pjok-uji@tebakaksi.test', 'password' => 'password', 'peran' => 'guru', 'jenis_guru' => 'mapel', 'status' => 'aktif']);
        $mapel->kelasMapel()->attach($this->kelas->id);
        $ani = $this->murid('Ani');

        Livewire::actingAs($this->guru)->test(KegiatanHarianForm::class, ['kelas' => $this->kelas->id])
            ->call('simpan', [$ani->id => ['hadir' => true]]);
        Livewire::actingAs($mapel)->test(KegiatanHarianForm::class, ['kelas' => $this->kelas->id])
            ->call('simpan', [$ani->id => ['hadir' => true, 'jurnal' => true]]);

        $this->assertSame(1, KegiatanHarian::count());
        $this->assertEqualsCanonicalizing([$this->guru->id, $mapel->id], KegiatanHarian::first()->riwayat()->pluck('user_id')->all());
    }

    public function test_guru_tidak_bisa_mengubah_catatan_lebih_dari_batas_hari(): void
    {
        $lama = today()->subDays(10)->toDateString();

        Livewire::actingAs($this->guru)
            ->withQueryParams(['tanggal' => $lama])
            ->test(KegiatanHarianForm::class, ['kelas' => $this->kelas->id])
            ->assertSet('tanggal', $lama)
            ->call('simpan', [$this->murid('Ani')->id => ['hadir' => true]])
            ->assertForbidden();

        $this->assertSame(0, KegiatanHarian::count());
    }

    public function test_rekap_menghitung_dari_hari_kegiatan_dan_mengabaikan_hari_libur(): void
    {
        // 5–9 Oktober 2026 = 5 hari kegiatan; Kamis 8 Oktober libur
        \App\Models\HariLibur::create(['tanggal' => '2026-10-08', 'keterangan' => 'Libur']);
        $ani = $this->murid('Ani');
        foreach (['2026-10-05', '2026-10-06', '2026-10-07'] as $tgl) {
            $k = KegiatanHarian::create(['kelas_id' => $this->kelas->id, 'tanggal' => $tgl]);
            $k->catatanMurid()->create(['murid_id' => $ani->id, 'hadir' => true, 'jurnal' => $tgl !== '2026-10-07']);
        }

        $rekap = Rekap::rekapKelas($this->kelas, Carbon::parse('2026-10-05'), Carbon::parse('2026-10-09'));
        $baris = $rekap['murid']->firstWhere('murid.id', $ani->id);

        $this->assertSame(4, $rekap['n']);
        $this->assertSame(3, $baris['hadir']);
        $this->assertSame(75.0, $baris['hadir_persen']);
        $this->assertSame(2, $baris['jurnal']);
        $this->assertTrue($baris['aktif']);
    }

    public function test_kasus_pembinaan_dicatat_dipantau_dan_ditutup(): void
    {
        $budi = $this->murid('Budi');

        Livewire::actingAs($this->guru)->test(PembinaanForm::class)
            ->set('muridId', (string) $budi->id)
            ->set('kondisiAwal', 'Sering mengganggu teman saat membaca')
            ->set('pelanggaran', 'lain')
            ->set('pelanggaranLain', 'Bermain gawai saat membaca')
            ->set('tingkat', 'ringan')
            ->set('tindakan', (string) TindakanPembinaan::first()->id)
            ->call('simpan')
            ->assertHasNoErrors();

        $kasus = KasusPembinaan::firstOrFail();
        $this->assertSame('Bermain gawai saat membaca', $kasus->namaPelanggaran());
        $this->assertSame([], $kasus->mingguBelumDipantau());

        // Delapan hari kemudian minggu 1 dan 2 sudah dimulai
        Carbon::setTestNow('2026-10-22 08:00:00');
        $kasus->refresh();
        $this->assertSame([1, 2], $kasus->mingguBelumDipantau());

        Livewire::actingAs($this->guru)->test(PembinaanDetail::class, ['kasus' => $kasus])
            ->assertSet('minggu', 1)
            ->set('catatan', 'Mulai tertib saat membaca')
            ->call('pantau', 'mulai')
            ->assertSet('minggu', 2)
            ->call('tetapkanStatus', 'positif');

        $kasus->refresh();
        $this->assertSame('positif', $kasus->status_akhir);
        $this->assertSame('mulai', $kasus->pemantauan()->where('minggu', 1)->value('tingkat'));
        $this->assertSame('Mulai tertib saat membaca', $kasus->pemantauan()->where('minggu', 1)->value('catatan'));
    }

    public function test_guru_tidak_bisa_mencatat_kasus_untuk_murid_kelas_lain(): void
    {
        $lain = Kelas::create(['tahun_ajaran_id' => $this->kelas->tahun_ajaran_id, 'tingkat' => 5, 'nama' => '5A']);
        $dina = Murid::create(['nama' => 'Dina']);
        $lain->murid()->attach($dina->id);

        Livewire::actingAs($this->guru)->test(PembinaanForm::class)
            ->set('muridId', (string) $dina->id)
            ->set('kondisiAwal', 'x')
            ->set('pelanggaran', (string) JenisPelanggaran::first()->id)
            ->set('tingkat', 'ringan')
            ->set('tindakan', (string) TindakanPembinaan::first()->id)
            ->call('simpan')
            ->assertHasErrors('muridId');

        $this->assertSame(0, KasusPembinaan::count());
    }

    public function test_kondisi_awal_opsional_dan_pemantauan_cepat_dari_beranda(): void
    {
        $ani = $this->murid('Ani');
        $jenis = JenisPelanggaran::first();

        $form = Livewire::actingAs($this->guru)->test(PembinaanForm::class)
            ->set('muridId', (string) $ani->id)
            ->set('pelanggaran', (string) $jenis->id)
            ->assertSee('Pakai: "'.$jenis->nama.'"', false)
            // Saran tidak mengisi sendiri; kondisi awal tetap kosong bila tidak diketuk
            ->assertSet('kondisiAwal', '')
            ->set('tingkat', 'ringan')
            ->set('tindakan', (string) TindakanPembinaan::first()->id)
            ->call('simpan')
            ->assertHasNoErrors();
        $kasus = KasusPembinaan::firstOrFail();
        $this->assertNull($kasus->kondisi_awal);

        Carbon::setTestNow('2026-10-16 08:00:00');
        Livewire::actingAs($this->guru)->test(Beranda::class)
            ->assertSee('Perkembangan minggu ke-1')
            ->call('pantau', $kasus->id, 'mulai')
            ->assertDontSee('Perkembangan minggu ke-1');
        $this->assertSame('mulai', $kasus->pemantauan()->where('minggu', 1)->value('tingkat'));

        // Guru lain tidak bisa memantau kasus kelas ini
        $lain = User::create(['name' => 'Guru Lain', 'email' => 'lain@tebakaksi.test', 'password' => 'password', 'peran' => 'guru', 'jenis_guru' => 'kelas', 'status' => 'aktif']);
        Livewire::actingAs($lain)->test(Beranda::class)->call('pantau', $kasus->id, 'positif')->assertForbidden();
    }

    public function test_kegiatan_harian_menampilkan_mode_satu_kolom(): void
    {
        $this->actingAs($this->guru)->get('/guru/kegiatan')->assertOk()
            ->assertSee('Pilih yang sedang dicentang')
            ->assertSee('Belum dicentang Hadir');
    }
}
