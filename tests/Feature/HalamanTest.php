<?php

namespace Tests\Feature;

use App\Models\KaryaLiterasi;
use App\Models\KasusPembinaan;
use App\Models\Kelas;
use App\Models\User;
use Database\Seeders\DataAwalSeeder;
use Database\Seeders\DataContohSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Memastikan setiap halaman terbuka tanpa galat untuk peran yang berhak, dan tertutup untuk yang tidak. */
class HalamanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([DataAwalSeeder::class, DataContohSeeder::class]);
    }

    public function test_halaman_publik_terbuka(): void
    {
        foreach (['/', '/berita', '/galeri', '/diseminasi', '/manual-book', '/pengaduan', '/masuk', '/daftar', '/halaman/tentang-tebak-aksi'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_halaman_guru_terbuka(): void
    {
        $guru = User::where('email', 'guru1@tebakaksi.test')->first();
        $kelas = $guru->kelasAkses()->first();
        $kasus = KasusPembinaan::where('kelas_id', $kelas->id)->first();
        $karya = KaryaLiterasi::where('kelas_id', $kelas->id)->first();

        $this->actingAs($guru);
        $halaman = [
            '/guru', '/guru/kegiatan', '/guru/kegiatan?tanggal='.today()->subDays(3)->toDateString(),
            '/guru/pembinaan', '/guru/pembinaan?tab=perlu', '/guru/pembinaan?tab=selesai', '/guru/pembinaan/baru',
            "/guru/pembinaan/{$kasus->id}", "/guru/pembinaan/{$kasus->id}/ubah",
            '/guru/karya', '/guru/karya/baru', "/guru/karya/{$karya->id}/ubah", '/akun',
        ];
        foreach (['budaya', 'literasi', 'pelanggaran', 'ekosistem'] as $lap) {
            $halaman[] = '/guru/rekap?bulan='.today()->format('Y-m').'&laporan='.$lap;
        }
        foreach ($halaman as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_guru_tidak_bisa_membuka_kelas_lain_dan_halaman_admin(): void
    {
        $guru = User::where('email', 'guru1@tebakaksi.test')->first();
        $kelasLain = Kelas::where('wali_kelas_id', '!=', $guru->id)->first();
        $kasusLain = KasusPembinaan::where('kelas_id', $kelasLain->id)->first();

        $this->actingAs($guru);
        $this->get("/guru/pembinaan/{$kasusLain->id}")->assertForbidden();
        $this->get("/rekap/unduh?kelas={$kelasLain->id}&bulan=".today()->format('Y-m').'&laporan=budaya&format=pdf')->assertForbidden();
        $this->get('/monev')->assertForbidden();
        $this->get('/admin')->assertForbidden();
    }

    public function test_pendaftar_belum_disetujui_tidak_bisa_masuk(): void
    {
        $this->post('/masuk', ['email' => 'pendaftar@tebakaksi.test', 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_kepsek_membuka_monev_dan_rekap(): void
    {
        $this->actingAs(User::where('email', 'kepsek@tebakaksi.test')->first());
        $this->get('/monev')->assertOk();
        $this->get('/monev?periode=semester&semester=1')->assertOk();
        $this->get('/guru/rekap')->assertOk();
        $this->get('/guru/kegiatan')->assertForbidden();
        $this->get('/admin')->assertForbidden();
        $this->get('/rekap/unduh?bulan='.today()->format('Y-m').'&laporan=pelanggaran&format=pdf')->assertOk();
        $this->get('/rekap/unduh?bulan='.today()->format('Y-m').'&laporan=ekosistem&format=xlsx')->assertOk();
    }

    public function test_admin_membuka_semua_halaman_admin(): void
    {
        $this->actingAs(User::where('email', 'admin@tebakaksi.test')->first());
        $halaman = ['', 'aspek-karakter', 'diseminasi', 'galeri', 'hari-libur', 'jenis-karya', 'jenis-kegiatan', 'jenis-pelanggaran',
            'karya-literasi', 'kasus-pembinaan', 'kelas', 'konten', 'manual-book', 'murid', 'pengaduan', 'pengaturan', 'pengguna',
            'tahun-ajaran', 'tindakan-pembinaan'];
        foreach ($halaman as $p) {
            $this->get('/admin/'.$p)->assertOk();
        }
        $this->get('/monev')->assertOk();
        $this->get('/guru/kegiatan')->assertOk();
    }
}
