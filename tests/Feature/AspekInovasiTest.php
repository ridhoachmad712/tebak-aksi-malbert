<?php

namespace Tests\Feature;

use App\Models\JenisPelanggaran;
use App\Models\KaryaLiterasi;
use App\Models\KasusPembinaan;
use App\Models\KegiatanHarian;
use App\Models\Kelas;
use App\Models\Murid;
use App\Models\TahunAjaran;
use App\Models\TindakanPembinaan;
use App\Models\User;
use App\Services\AspekInovasi;
use App\Services\Rekap;
use Database\Seeders\DataAwalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AspekInovasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_lima_aspek_dihitung_per_jumlah_murid(): void
    {
        Carbon::setTestNow('2026-10-15 09:00:00');
        $this->seed(DataAwalSeeder::class);
        $ta = TahunAjaran::create(['nama' => '2026/2027', 'mulai' => '2026-07-13', 'selesai' => '2027-06-30', 'aktif' => true]);
        $kelas = Kelas::create(['tahun_ajaran_id' => $ta->id, 'tingkat' => 4, 'nama' => '4A']);
        $m = [];
        foreach (['Ani', 'Budi', 'Citra', 'Dodi'] as $nama) {
            $m[$nama] = Murid::create(['nama' => $nama]);
            $kelas->murid()->attach($m[$nama]->id);
        }

        // September: Ani lengkap, Budi Budaya Membaca saja, Citra hanya hadir, Dodi tidak tercatat
        foreach (Rekap::hariKegiatan(Carbon::parse('2026-09-01'), Carbon::parse('2026-09-30')) as $hari) {
            $k = KegiatanHarian::create(['kelas_id' => $kelas->id, 'tanggal' => $hari->toDateString()]);
            $k->catatanMurid()->createMany([
                ['murid_id' => $m['Ani']->id, 'hadir' => true, 'konsisten' => true, 'jurnal' => true, 'memahami' => true, 'merangkum' => true, 'menyampaikan' => true],
                ['murid_id' => $m['Budi']->id, 'hadir' => true, 'konsisten' => true, 'jurnal' => true],
                ['murid_id' => $m['Citra']->id, 'hadir' => true],
            ]);
        }
        $karya = KaryaLiterasi::create(['kelas_id' => $kelas->id, 'tanggal' => '2026-09-10', 'jenis_karya_lain' => 'Puisi', 'judul' => 'Teras', 'user_id' => User::first()->id]);
        $karya->murid()->attach($m['Ani']->id);

        $kasus = fn ($murid, $tgl, $status = null) => KasusPembinaan::create([
            'murid_id' => $m[$murid]->id, 'kelas_id' => $kelas->id, 'tanggal' => $tgl,
            'jenis_pelanggaran_id' => JenisPelanggaran::first()->id, 'tingkat' => 'ringan',
            'tindakan_pembinaan_id' => TindakanPembinaan::first()->id, 'status_akhir' => $status,
        ]);
        // Agustus: 3 murid berbeda melanggar
        $kasus('Ani', '2026-08-04', 'konsisten');
        $kasus('Budi', '2026-08-05', 'positif');
        $kasus('Citra', '2026-08-06', 'positif');
        // September: Dodi (minggu 1 dan 2) dan Citra (minggu 4) → 2 murid berbeda
        $kasus('Dodi', '2026-09-03', 'belum');
        $kasus('Dodi', '2026-09-10', 'positif');
        $kasus('Citra', '2026-09-23', 'belum');

        $hasil = collect(AspekInovasi::bulan('2026-09')['aspek'])->keyBy('nama');

        $this->assertSame('2 dari 4 murid', $hasil['Budaya Membaca']['capaian']);
        $this->assertSame(50.0, $hasil['Budaya Membaca']['nilai']);
        $this->assertSame(25.0, $hasil['Kemampuan Literasi']['nilai']);

        $this->assertSame(33.3, $hasil['Penguatan Karakter']['nilai']);
        $this->assertSame('Minggu 1: 1 · Minggu 2: 1 · Minggu 3: 0 · Minggu 4: 1', $hasil['Penguatan Karakter']['rincian']);
        $this->assertSame('tercapai', $hasil['Penguatan Karakter']['status']);

        // Kasus terakhir Dodi positif, Citra belum berubah
        $this->assertSame('1 dari 2 murid binaan', $hasil['Kepatuhan Perilaku']['capaian']);
        $this->assertSame(50.0, $hasil['Kepatuhan Perilaku']['nilai']);

        $this->assertSame(75.0, $hasil['Ekosistem Literat']['nilai']);
        $this->assertSame(25.0, $hasil['Ekosistem Literat']['nilai2']);
        $this->assertSame('kurang', $hasil['Ekosistem Literat']['status']);

        // Tampil di Dashboard kepala sekolah untuk bulan yang dipilih
        $kepsek = User::create(['name' => 'Kepala Uji', 'email' => 'kepala@tebakaksi.test', 'password' => 'password', 'peran' => 'kepsek', 'status' => 'aktif']);
        $this->actingAs($kepsek)->get('/dashboard?bulan=2026-09')->assertOk()
            ->assertSee('Capaian 5 aspek inovasi')
            ->assertSee('September 2026: 1 aspek tercapai, 4 aspek belum tercapai.')
            ->assertSee('kurang 2 murid lagi')
            ->assertSee('Tidak berubah dalam sebulan')
            ->assertSee('Minggu 1: 1 · Minggu 2: 1 · Minggu 3: 0 · Minggu 4: 1');
    }

    public function test_bulan_bawaan_bulan_lalu_selama_bulan_berjalan_belum_selesai(): void
    {
        $this->seed(DataAwalSeeder::class);
        TahunAjaran::create(['nama' => '2026/2027', 'mulai' => '2026-07-13', 'selesai' => '2027-06-30', 'aktif' => true]);

        // Pertengahan Oktober: masih ada hari kegiatan tersisa, jadi yang tampil September
        Carbon::setTestNow('2026-10-15 09:00:00');
        $this->assertSame('2026-09', Rekap::bulanBawaan());

        // Jumat 30 Oktober adalah hari kegiatan terakhir bulan itu, jadi Oktober sudah dianggap selesai
        Carbon::setTestNow('2026-10-30 13:00:00');
        $this->assertSame('2026-10', Rekap::bulanBawaan());

        // Bulan pertama tahun ajaran: bulan lalu belum termasuk tahun ajaran, jadi tetap bulan berjalan
        Carbon::setTestNow('2026-07-20 09:00:00');
        $this->assertSame('2026-07', Rekap::bulanBawaan());

        // Halaman depan dan Dashboard kepala sekolah memakai bulan bawaan; guru tetap melihat bulan berjalan di Rekap
        Carbon::setTestNow('2026-10-15 09:00:00');
        $this->get('/')->assertOk()->assertSee('September 2026 dalam angka');
        $kepsek = User::create(['name' => 'Kepala Uji', 'email' => 'kepala@tebakaksi.test', 'password' => 'password', 'peran' => 'kepsek', 'status' => 'aktif']);
        $this->actingAs($kepsek)->get('/dashboard')->assertOk()->assertSee('September 2026:');
        $this->get('/monev')->assertOk()->assertSee('Seluruh sekolah · September 2026');
    }
}
