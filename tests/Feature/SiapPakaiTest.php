<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CadanganData;
use Database\Seeders\DataAwalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Hal-hal yang wajib beres sebelum aplikasi dipakai guru: bahasa, halaman galat, dan cadangan. */
class SiapPakaiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DataAwalSeeder::class);
    }

    public function test_pesan_validasi_berbahasa_indonesia(): void
    {
        $this->post('/daftar', [])->assertSessionHasErrors(['name' => 'Nama wajib diisi.']);
        $this->post('/masuk', ['email' => 'admin@tebakaksi.test', 'password' => 'salah'])
            ->assertSessionHasErrors(['email' => 'Email atau kata sandi tidak sesuai.']);
    }

    public function test_halaman_galat_berbahasa_indonesia(): void
    {
        $this->get('/halaman-yang-tidak-ada')->assertNotFound()->assertSee('Halaman tidak ditemukan');

        $guru = User::create(['name' => 'Guru', 'email' => 'g@tebakaksi.test', 'password' => 'password', 'peran' => 'guru', 'jenis_guru' => 'kelas', 'status' => 'aktif']);
        $this->actingAs($guru)->get('/monev')->assertForbidden()->assertSee('Halaman ini tidak tersedia untuk akun Anda');
    }

    public function test_cadangan_sql_berisi_struktur_dan_data_tanpa_sesi(): void
    {
        $isi = '';
        CadanganData::tulisSql(function (string $teks) use (&$isi) {
            $isi .= $teks;
        });

        $this->assertStringContainsString('CREATE TABLE "users"', $isi);
        $this->assertStringContainsString('admin@tebakaksi.test', $isi);
        $this->assertStringContainsString('CREATE TABLE "sessions"', $isi);
        $this->assertStringNotContainsString('INSERT INTO "sessions"', $isi);
    }

    public function test_halaman_cadangan_hanya_untuk_admin(): void
    {
        $this->actingAs(User::where('peran', 'admin')->first())->get('/admin/cadangan')->assertOk()->assertSee('Unduh data (.sql)');

        $guru = User::create(['name' => 'Guru', 'email' => 'g2@tebakaksi.test', 'password' => 'password', 'peran' => 'guru', 'jenis_guru' => 'kelas', 'status' => 'aktif']);
        $this->actingAs($guru)->get('/admin/cadangan')->assertForbidden();
    }

    public function test_arsip_berkas_dapat_dibuat(): void
    {
        $lokasi = CadanganData::buatZipBerkas();

        $this->assertFileExists($lokasi);
        unlink($lokasi);
    }
}
