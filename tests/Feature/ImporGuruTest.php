<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\ImporGuru;
use Database\Seeders\DataAwalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ImporGuruTest extends TestCase
{
    use RefreshDatabase;

    public function test_impor_guru_membuat_akun_dan_mengatur_kelas(): void
    {
        $this->seed(DataAwalSeeder::class);
        $ta = TahunAjaran::create(['nama' => '2026/2027', 'mulai' => '2026-07-13', 'selesai' => '2027-06-30', 'aktif' => true]);
        $lama = User::create(['name' => 'Guru Lama', 'email' => 'lama@tebakaksi.test', 'password' => 'password', 'peran' => 'guru', 'jenis_guru' => 'kelas', 'status' => 'aktif']);
        $k1 = Kelas::create(['tahun_ajaran_id' => $ta->id, 'tingkat' => 1, 'nama' => '1A']);
        $k2 = Kelas::create(['tahun_ajaran_id' => $ta->id, 'tingkat' => 2, 'nama' => '2A', 'wali_kelas_id' => $lama->id]);
        $k4 = Kelas::create(['tahun_ajaran_id' => $ta->id, 'tingkat' => 4, 'nama' => '4A']);
        $k5 = Kelas::create(['tahun_ajaran_id' => $ta->id, 'tingkat' => 5, 'nama' => '5A']);

        $hasil = ImporGuru::dariBaris([
            ['Nama', 'Email', 'NIP', 'No HP', 'Jenis Guru', 'Kelas'],
            ['Guru Satu, S.Pd.', 'Satu@Tebakaksi.test', '0198', '0812', 'Kelas', 'Kelas 1A'],
            ['Guru Dua', 'dua@tebakaksi.test', '', '', 'Mapel', '4A, 5a, 9Z'],
            ['Guru Tiga', 'tiga@tebakaksi.test', '', '', '', '2A'],
            ['Guru Lama', 'lama@tebakaksi.test', '', '', 'Kelas', ''],
            ['Tanpa Email', '', '', '', 'Kelas', ''],
            ['', '', '', '', '', ''],
        ], 'sandiawal123');

        $this->assertSame(3, $hasil['baru']);
        $this->assertSame(['Guru Lama'], $hasil['sudah_ada']);
        $this->assertCount(1, $hasil['dilewati']);
        $this->assertStringContainsString('9Z tidak ditemukan', implode(' ', $hasil['catatan']));
        $this->assertStringContainsString('Kelas 2A sudah punya wali kelas', implode(' ', $hasil['catatan']));

        $satu = User::where('email', 'satu@tebakaksi.test')->firstOrFail();
        $this->assertSame('aktif', $satu->status);
        $this->assertSame('0198', $satu->nip);
        $this->assertTrue(Hash::check('sandiawal123', $satu->password));
        $this->assertSame($satu->id, $k1->fresh()->wali_kelas_id);

        $dua = User::where('email', 'dua@tebakaksi.test')->firstOrFail();
        $this->assertSame('mapel', $dua->jenis_guru);
        $this->assertEqualsCanonicalizing([$k4->id, $k5->id], $dua->kelasMapel()->pluck('kelas.id')->all());

        // Wali kelas yang sudah ada tidak ditimpa
        $this->assertSame($lama->id, $k2->fresh()->wali_kelas_id);
    }

    public function test_impor_dari_berkas_excel(): void
    {
        $this->seed(DataAwalSeeder::class);
        $path = sys_get_temp_dir().'/impor-guru-'.uniqid().'.xlsx';
        $writer = new \OpenSpout\Writer\XLSX\Writer;
        $writer->openToFile($path);
        $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues(['Nama', 'Email', 'NIP', 'No HP', 'Jenis Guru', 'Kelas']));
        $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues(['Guru Berkas', 'berkas@tebakaksi.test', '', '', 'Mapel', '']));
        $writer->close();

        $hasil = ImporGuru::dariBerkas($path, 'sandiawal123');
        @unlink($path);

        $this->assertSame(1, $hasil['baru']);
        $this->assertSame('mapel', User::where('email', 'berkas@tebakaksi.test')->value('jenis_guru'));
    }

    public function test_templat_dan_tombol_impor_tersedia(): void
    {
        $this->seed(DataAwalSeeder::class);
        $this->assertFileExists(public_path('templat/templat-impor-guru.xlsx'));

        $this->actingAs(User::where('peran', 'admin')->first())
            ->get('/admin/pengguna')->assertOk()->assertSee('Impor guru dari Excel');
    }
}
