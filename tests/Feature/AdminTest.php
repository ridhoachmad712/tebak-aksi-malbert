<?php

namespace Tests\Feature;

use App\Filament\Pages\PengaturanTarget;
use App\Filament\Resources\KelasResource\Pages\ManageKelas;
use App\Filament\Support\HapusAman;
use App\Filament\Resources\PenggunaResource\Pages\ManagePengguna;
use App\Models\BaselinePelanggaran;
use App\Models\KegiatanHarian;
use App\Models\Kelas;
use App\Models\Murid;
use App\Models\Pengaturan;
use App\Models\TahunAjaran;
use App\Models\User;
use Database\Seeders\DataAwalSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DataAwalSeeder::class);
        $this->admin = User::where('peran', 'admin')->first();
        $this->actingAs($this->admin);
    }

    public function test_admin_menyetujui_guru_dan_menetapkan_kelas(): void
    {
        $ta = TahunAjaran::create(['nama' => '2026/2027', 'mulai' => '2026-07-13', 'selesai' => '2027-06-30', 'aktif' => true]);
        $kelas = Kelas::create(['tahun_ajaran_id' => $ta->id, 'tingkat' => 2, 'nama' => '2A']);
        $pendaftar = User::create(['name' => 'Guru Baru', 'email' => 'baru@tebakaksi.test', 'password' => 'password', 'peran' => 'guru', 'jenis_guru' => 'kelas', 'status' => 'menunggu']);

        Livewire::test(ManagePengguna::class)
            ->callAction(TestAction::make('setujui')->table($pendaftar), data: ['jenis_guru' => 'kelas', 'kelas_wali_id' => $kelas->id])
            ->assertHasNoActionErrors();

        $this->assertSame('aktif', $pendaftar->fresh()->status);
        $this->assertSame($pendaftar->id, $kelas->fresh()->wali_kelas_id);
        $this->assertTrue($pendaftar->fresh()->bolehAksesKelas($kelas));
    }

    public function test_data_yang_punya_catatan_tidak_bisa_dihapus(): void
    {
        $ta = TahunAjaran::create(['nama' => '2026/2027', 'mulai' => '2026-07-13', 'selesai' => '2027-06-30', 'aktif' => true]);
        $kelas = Kelas::create(['tahun_ajaran_id' => $ta->id, 'tingkat' => 1, 'nama' => '1A']);
        $murid = Murid::create(['nama' => 'Ani']);
        $kelas->murid()->attach($murid->id);
        $kegiatan = KegiatanHarian::create(['kelas_id' => $kelas->id, 'tanggal' => '2026-10-14']);
        $kegiatan->catatanMurid()->create(['murid_id' => $murid->id, 'hadir' => true]);

        $this->assertNotNull(HapusAman::alasan($kelas));
        $this->assertNotNull(HapusAman::alasan($murid));
        $this->assertNotNull(HapusAman::alasan($ta));
        $this->assertNotNull(HapusAman::alasan($this->admin));

        Livewire::test(ManageKelas::class)->callAction(TestAction::make('delete')->table($kelas));
        $this->assertModelExists($kelas);

        // Lapisan database juga menolak penghapusan berantai
        $this->expectException(QueryException::class);
        $kelas->delete();
    }

    public function test_admin_menyimpan_target_dan_baseline(): void
    {
        $ta = TahunAjaran::create(['nama' => '2026/2027', 'mulai' => '2026-07-13', 'selesai' => '2027-06-30', 'aktif' => true]);

        Livewire::test(PengaturanTarget::class)
            ->set('data.target_murid_aktif', 90)
            ->set('data.batas_centang_cetak', 80)
            ->set('data.baseline_1', 45)
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertSame(90.0, Pengaturan::angka('target_murid_aktif'));
        $this->assertSame(80.0, Pengaturan::angka('batas_centang_cetak'));
        $this->assertSame(45, BaselinePelanggaran::where('tahun_ajaran_id', $ta->id)->where('semester', 1)->value('jumlah'));
    }
}
