<?php

namespace Tests\Feature;

use App\Filament\Support\HapusAman;
use App\Models\Kelas;
use App\Models\Pengaturan;
use App\Models\TahunAjaran;
use App\Models\User;
use Database\Seeders\DataAwalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class KeteranganRekonstruksiTest extends TestCase
{
    use RefreshDatabase;

    protected TahunAjaran $ta;

    protected Kelas $kelas;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-21 12:00:00');
        $this->seed(DataAwalSeeder::class);
        $this->ta = TahunAjaran::create(['nama' => '2026/2027', 'mulai' => '2026-07-13', 'selesai' => '2027-06-25', 'aktif' => true]);
        $this->kelas = Kelas::create(['tahun_ajaran_id' => $this->ta->id, 'tingkat' => 3, 'nama' => '3A']);
    }

    public function test_keterangan_rekonstruksi_tidak_ditampilkan(): void
    {
        Pengaturan::simpan(['data_awal_rekonstruksi_bulan' => '2026-09', 'data_awal_rekonstruksi_catatan' => 'Catatan uji pita.']);
        $this->actingAs(User::where('peran', 'admin')->first());

        $this->get('/monev?bulan=2026-09')->assertOk()->assertDontSee('Data awal rekonstruksi')->assertDontSee('Catatan uji pita.');
        $this->get('/guru/rekap?kelas='.$this->kelas->id.'&bulan=2026-09')->assertOk()->assertDontSee('Data awal rekonstruksi');
        $this->get('/guru/kegiatan/'.$this->kelas->id.'?tanggal=2026-09-15')->assertOk()->assertDontSee('Data awal rekonstruksi');
        $this->get('/')->assertOk()->assertDontSee('Data awal rekonstruksi');
    }

    public function test_hapus_aman_mengenali_tabel_rekonsiliasi_dan_rekap_historis(): void
    {
        $this->assertNull(HapusAman::alasan($this->kelas));

        DB::table('rencana_rekonsiliasi_kelas')->insert([
            'kelas_id' => $this->kelas->id, 'bulan' => '2026-09-01', 'data' => '{}', 'status' => 'perlu_verifikasi',
        ]);
        $this->assertStringContainsString('rencana rekonsiliasi', HapusAman::alasan($this->kelas));

        $taKosong = TahunAjaran::create(['nama' => '2025/2026', 'mulai' => '2025-07-14', 'selesai' => '2026-06-26', 'aktif' => false]);
        $this->assertNull(HapusAman::alasan($taKosong));
        DB::table('rekap_historis_bulanan')->insert([
            'tahun_ajaran_id' => $taKosong->id, 'bulan' => '2026-05-01', 'data' => '{}', 'sumber' => 'uji',
        ]);
        $this->assertStringContainsString('rekap historis bulanan', HapusAman::alasan($taKosong));
    }
}
