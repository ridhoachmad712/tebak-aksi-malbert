<?php

namespace Tests\Feature;

use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\AspekInovasi;
use App\Services\Rekap;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RekapHistorisTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-21 12:00:00');
    }

    protected function simpanHistoris(): TahunAjaran
    {
        $ta = TahunAjaran::create(['nama' => '2026/2027', 'mulai' => '2026-07-13', 'selesai' => '2027-06-25', 'aktif' => true]);
        DB::table('rekap_historis_bulanan')->insert([
            'tahun_ajaran_id' => $ta->id,
            'bulan' => '2026-09-01',
            'sumber' => 'HASIL.docx dan konfirmasi pemilik',
            'catatan' => 'Rincian individual dan bukti karya masih dilengkapi.',
            'data' => json_encode([
                'jumlah_murid' => 380, 'jumlah_kelas' => 14, 'hari_kegiatan' => 22,
                'budaya' => 336, 'literasi' => 320, 'murid_aktif' => 380,
                'murid_binaan' => 57, 'murid_berubah' => 51,
                'pelanggar_mingguan' => [21, 17, 12, 7], 'pelanggar_bulan_sebelumnya' => null,
                'jumlah_karya' => 400, 'murid_berkarya' => null,
                'target_budaya' => 80, 'target_literasi' => 80, 'target_aktif' => 85,
                'target_pembinaan' => 80, 'target_penurunan' => 20, 'target_berkarya' => 100,
            ]),
        ]);

        return $ta;
    }

    public function test_rekap_sekolah_dibaca_tanpa_membuat_catatan_individual(): void
    {
        $this->simpanHistoris();
        $hasil = AspekInovasi::bulan('2026-09');
        $aspek = collect($hasil['aspek'])->keyBy('nama');

        $this->assertSame(380, $hasil['jumlah_murid']);
        $this->assertSame(88.4, $aspek['Budaya Membaca']['nilai']);
        $this->assertSame(84.2, $aspek['Kemampuan Literasi']['nilai']);
        $this->assertSame(89.5, $aspek['Kepatuhan Perilaku']['nilai']);
        $this->assertSame(100.0, $aspek['Ekosistem Literat']['nilai']);
        $this->assertStringContainsString('400 karya dilaporkan', $aspek['Ekosistem Literat']['capaian']);
        $this->assertNull($aspek['Penguatan Karakter']['nilai']);
        $this->assertNull($aspek['Ekosistem Literat']['nilai2']);
        $this->assertSame('belum_cukup', $aspek['Ekosistem Literat']['status']);
        $this->assertSame(3, $hasil['dinilai']);
        $this->assertSame(3, $hasil['tercapai']);

        foreach (['kegiatan_harian', 'kegiatan_harian_murid', 'kasus_pembinaan', 'pemantauan_kasus', 'karya_literasi'] as $tabel) {
            $this->assertDatabaseCount($tabel, 0);
        }

        $kepsek = User::create(['name' => 'Kepala Uji', 'email' => 'kepala-historis@tebakaksi.test', 'password' => 'password', 'peran' => 'kepsek', 'status' => 'aktif']);
        $this->actingAs($kepsek)->get('/dashboard?bulan=2026-09')->assertOk()
            ->assertDontSee('rekap historis sekolah')
            ->assertSee('400 karya dilaporkan')
            ->assertSee('Kepemilikan karya per murid masih perlu dilengkapi');
    }

    public function test_rekap_historis_tidak_mengubah_bulan_lain_atau_tahun_ajaran_lain(): void
    {
        $this->simpanHistoris();
        $this->assertArrayNotHasKey('sumber_historis', AspekInovasi::bulan('2026-10'));

        TahunAjaran::create(['nama' => '2027/2028', 'mulai' => '2027-07-12', 'selesai' => '2028-06-30', 'aktif' => true]);
        app()->forgetScopedInstances();
        $this->assertArrayNotHasKey('sumber_historis', AspekInovasi::bulan('2026-09'));
    }

    public function test_pemerataan_karya_baru_dinilai_setelah_jumlah_pemilik_diketahui(): void
    {
        $this->simpanHistoris();
        $r = DB::table('rekap_historis_bulanan')->first();
        $data = json_decode($r->data, true);
        $data['murid_berkarya'] = 380;
        DB::table('rekap_historis_bulanan')->where('id', $r->id)->update(['data' => json_encode($data)]);

        $hasil = AspekInovasi::bulan('2026-09');
        $ekosistem = collect($hasil['aspek'])->firstWhere('nama', 'Ekosistem Literat');
        $this->assertSame('tercapai', $ekosistem['status']);
        $this->assertSame(100.0, $ekosistem['nilai2']);
        $this->assertSame(4, $hasil['dinilai']);
    }
}
