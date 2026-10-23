<?php

namespace Tests\Feature;

use App\Filament\Resources\KaryaLiterasiResource\Pages\ManageKaryaLiterasi;
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
use Livewire\Livewire;
use Tests\TestCase;

class KaryaAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-21 09:00:00');
        $this->seed(DataAwalSeeder::class);
    }

    protected function karya(Kelas $kelas, string $judul, string $tanggal, ?Murid $murid, ?User $guru = null): KaryaLiterasi
    {
        $karya = KaryaLiterasi::create([
            'kelas_id' => $kelas->id, 'tanggal' => $tanggal, 'jenis_karya_id' => JenisKarya::first()->id,
            'judul' => $judul, 'guru_pemilik_id' => $guru?->id, 'user_id' => User::first()->id,
        ]);
        if ($murid) {
            $karya->murid()->attach($murid->id);
        }

        return $karya;
    }

    public function test_admin_menyaring_karya_per_bulan_kelas_dan_nama_murid(): void
    {
        $ta = TahunAjaran::create(['nama' => '2026/2027', 'mulai' => '2026-07-13', 'selesai' => '2027-06-30', 'aktif' => true]);
        $kelas3 = Kelas::create(['tahun_ajaran_id' => $ta->id, 'tingkat' => 3, 'nama' => '3A']);
        $kelas5 = Kelas::create(['tahun_ajaran_id' => $ta->id, 'tingkat' => 5, 'nama' => '5A']);
        $ani = Murid::create(['nama' => 'Ani Contoh']);
        $budi = Murid::create(['nama' => 'Budi Contoh']);

        $puisi = $this->karya($kelas3, 'Puisi Pelangi', '2026-09-10', $ani);
        $rangkuman = $this->karya($kelas5, 'Rangkuman Kancil', '2026-10-05', $budi);
        $milikGuru = $this->karya($kelas3, 'Karya Milik Guru', '2026-10-06', null, User::first());

        $this->actingAs(User::where('peran', 'admin')->first());
        Livewire::test(ManageKaryaLiterasi::class)
            ->assertCanSeeTableRecords([$puisi, $rangkuman, $milikGuru])
            ->assertSee('Ani Contoh')
            ->filterTable('bulan', '2026-10')
            ->assertCanSeeTableRecords([$rangkuman, $milikGuru])->assertCanNotSeeTableRecords([$puisi])
            ->filterTable('pemilik', 'murid')
            ->assertCanSeeTableRecords([$rangkuman])->assertCanNotSeeTableRecords([$milikGuru]);

        Livewire::test(ManageKaryaLiterasi::class)
            ->filterTable('kelas_id', $kelas3->id)
            ->assertCanSeeTableRecords([$puisi])->assertCanNotSeeTableRecords([$rangkuman]);

        Livewire::test(ManageKaryaLiterasi::class)
            ->searchTable('Budi')
            ->assertCanSeeTableRecords([$rangkuman])->assertCanNotSeeTableRecords([$puisi]);
    }

    public function test_karya_literasi_tidak_ada_di_halaman_publik(): void
    {
        $this->get('/karya-literasi')->assertNotFound();

        // Menu publik yang sempat memuat Karya Literasi dibersihkan oleh migrasi
        Pengaturan::simpan(['menu_publik' => [
            ['tujuan' => 'beranda', 'label' => 'Beranda', 'tampil' => true],
            ['tujuan' => 'karya', 'label' => 'Karya Literasi', 'tampil' => true],
            ['tujuan' => 'galeri', 'label' => 'Galeri', 'tampil' => true],
        ]]);
        (require database_path('migrations/2026_10_23_000200_hapus_menu_publik_karya_literasi.php'))->up();

        $this->assertSame(['beranda', 'galeri'], array_column(Pengaturan::daftar('menu_publik'), 'tujuan'));
        $this->get('/')->assertOk()->assertDontSee('Karya Literasi');
    }
}
