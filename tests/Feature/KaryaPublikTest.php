<?php

namespace Tests\Feature;

use App\Models\JenisKarya;
use App\Models\KaryaLiterasi;
use App\Models\Kelas;
use App\Models\Murid;
use App\Models\Pengaturan;
use App\Models\TahunAjaran;
use App\Models\User;
use Database\Seeders\DataAwalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class KaryaPublikTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-21 09:00:00');
        $this->seed(DataAwalSeeder::class);
    }

    protected function karya(Kelas $kelas, string $judul, string $tanggal, ?Murid $murid, bool $foto = true, ?User $guru = null): KaryaLiterasi
    {
        $karya = KaryaLiterasi::create([
            'kelas_id' => $kelas->id, 'tanggal' => $tanggal, 'jenis_karya_id' => JenisKarya::first()->id,
            'judul' => $judul, 'guru_pemilik_id' => $guru?->id, 'user_id' => User::first()->id,
        ]);
        if ($murid) {
            $karya->murid()->attach($murid->id);
        }
        if ($foto) {
            $karya->foto()->create(['path' => 'karya/uji-'.$karya->id.'.jpg', 'user_id' => User::first()->id]);
        }

        return $karya;
    }

    public function test_karya_murid_tampil_di_halaman_publik_dengan_saringan(): void
    {
        $ta = TahunAjaran::create(['nama' => '2026/2027', 'mulai' => '2026-07-13', 'selesai' => '2027-06-30', 'aktif' => true]);
        $kelas3 = Kelas::create(['tahun_ajaran_id' => $ta->id, 'tingkat' => 3, 'nama' => '3A']);
        $kelas5 = Kelas::create(['tahun_ajaran_id' => $ta->id, 'tingkat' => 5, 'nama' => '5A']);
        $ani = Murid::create(['nama' => 'Ani Contoh']);
        $budi = Murid::create(['nama' => 'Budi Contoh']);
        $kelas3->murid()->attach($ani->id);
        $kelas5->murid()->attach($budi->id);

        $this->karya($kelas3, 'Puisi Pelangi', '2026-09-10', $ani);
        $this->karya($kelas5, 'Rangkuman Kancil', '2026-10-05', $budi, foto: false);
        $this->karya($kelas3, 'Karya Milik Guru', '2026-10-06', null, guru: User::first());

        // Tanpa masuk dan tanpa persetujuan admin, karya murid langsung tampil beserta nama pemiliknya
        $this->get('/karya-literasi')->assertOk()
            ->assertSee('Puisi Pelangi')->assertSee('Ani Contoh')
            ->assertSee('Rangkuman Kancil')->assertSee('Budi Contoh')->assertSee('Foto belum diunggah')
            ->assertDontSee('Karya Milik Guru')
            ->assertSee('2 karya');

        $this->get('/karya-literasi?kelas='.$kelas3->id)->assertOk()
            ->assertSee('Puisi Pelangi')->assertDontSee('Rangkuman Kancil');
        $this->get('/karya-literasi?bulan=2026-10')->assertOk()
            ->assertSee('Rangkuman Kancil')->assertDontSee('Puisi Pelangi');
        $this->get('/karya-literasi?bulan=2026-10&kelas='.$kelas3->id)->assertOk()
            ->assertSee('Belum ada karya untuk saringan ini');

        // Halaman depan: karya terbaru yang berfoto dan menu Karya Literasi
        $this->get('/')->assertOk()
            ->assertSee('Karya literasi terbaru')->assertSee('Puisi Pelangi')
            ->assertSee('href="'.route('karya.publik').'"', false);
    }

    public function test_menu_tersimpan_ditambah_karya_literasi_oleh_migrasi(): void
    {
        Pengaturan::simpan(['menu_publik' => [
            ['tujuan' => 'beranda', 'label' => 'Beranda', 'tampil' => true],
            ['tujuan' => 'galeri', 'label' => 'Galeri', 'tampil' => true],
            ['tujuan' => 'pengaduan', 'label' => 'Pengaduan', 'tampil' => true],
        ]]);

        $migrasi = require database_path('migrations/2026_10_23_000100_tambah_menu_karya_literasi.php');
        $migrasi->up();
        $migrasi->up();

        $this->assertSame(['beranda', 'galeri', 'karya', 'pengaduan'], array_column(Pengaturan::daftar('menu_publik'), 'tujuan'));
    }
}
