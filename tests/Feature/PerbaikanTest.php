<?php

namespace Tests\Feature;

use App\Filament\Resources\MuridResource\Pages\ManageMurid;
use App\Filament\Resources\TahunAjaranResource\Pages\ManageTahunAjaran;
use App\Models\KegiatanHarian;
use App\Models\Kelas;
use App\Models\Murid;
use App\Models\Pengaduan;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\Rekap;
use Database\Seeders\DataAwalSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class PerbaikanTest extends TestCase
{
    use RefreshDatabase;

    protected TahunAjaran $ta;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-14 08:00:00');
        $this->seed(DataAwalSeeder::class);
        $this->ta = TahunAjaran::create(['nama' => '2026/2027', 'mulai' => '2026-07-13', 'selesai' => '2027-06-30', 'aktif' => true]);
    }

    protected function guru(string $email, string $jenis = 'kelas'): User
    {
        return User::create(['name' => $email, 'email' => $email, 'password' => 'password', 'peran' => 'guru', 'jenis_guru' => $jenis, 'status' => 'aktif']);
    }

    public function test_tahun_ajaran_baru_menyalin_kelas_menaikkan_murid_dan_meluluskan_kelas_6(): void
    {
        $wali = $this->guru('wali4@tebakaksi.test');
        $k4 = Kelas::create(['tahun_ajaran_id' => $this->ta->id, 'tingkat' => 4, 'nama' => '4A', 'wali_kelas_id' => $wali->id]);
        $k5 = Kelas::create(['tahun_ajaran_id' => $this->ta->id, 'tingkat' => 5, 'nama' => '5A']);
        $k6 = Kelas::create(['tahun_ajaran_id' => $this->ta->id, 'tingkat' => 6, 'nama' => '6A']);
        $ani = Murid::create(['nama' => 'Ani']);
        $budi = Murid::create(['nama' => 'Budi']);
        $k4->murid()->attach($ani->id);
        $k6->murid()->attach($budi->id);

        $this->actingAs(User::where('peran', 'admin')->first());
        Livewire::test(ManageTahunAjaran::class)
            ->callAction('tahunAjaranBaru', data: [
                'nama' => '2027/2028', 'mulai' => '2027-07-12', 'selesai' => '2028-06-30',
                'salin_kelas' => true, 'salin_guru' => true, 'naikkan_murid' => true, 'luluskan_kelas_6' => true, 'aktifkan' => true,
            ])
            ->assertHasNoActionErrors();

        $baru = TahunAjaran::where('nama', '2027/2028')->firstOrFail();
        $this->assertTrue($baru->aktif);
        $this->assertFalse($this->ta->fresh()->aktif);
        $this->assertSame(3, $baru->kelas()->count());
        $this->assertSame('5A', $ani->kelasAktif()?->nama);
        $this->assertSame($wali->id, $baru->kelas()->where('nama', '4A')->value('wali_kelas_id'));
        $this->assertFalse($budi->fresh()->aktif);
        // Riwayat kelas lama tetap ada
        $this->assertTrue($k4->murid()->whereKey($ani->id)->exists());
    }

    public function test_impor_murid_dari_csv(): void
    {
        $kelas = Kelas::create(['tahun_ajaran_id' => $this->ta->id, 'tingkat' => 1, 'nama' => '1A']);
        $csv = UploadedFile::fake()->createWithContent('murid.csv', "Nama,NIS,NISN,JK\nAni Lestari,1001,,P\nBudi Santoso,1002,0099,L\n,,,\n");

        $this->actingAs(User::where('peran', 'admin')->first());
        Livewire::test(ManageMurid::class)
            ->callAction(TestAction::make('impor'), data: ['kelas_id' => $kelas->id, 'berkas' => $csv])
            ->assertHasNoActionErrors();

        $this->assertSame(2, $kelas->murid()->count());
        $this->assertSame('L', Murid::where('nama', 'Budi Santoso')->value('jenis_kelamin'));
        $this->assertSame('1001', Murid::where('nama', 'Ani Lestari')->value('nis'));
    }

    public function test_pengaduan_publik_tersimpan_dan_jebakan_bot_diabaikan(): void
    {
        $this->post('/pengaduan', ['kategori' => 'saran', 'isi' => 'Mohon tambah buku cerita bergambar untuk kelas satu.'])
            ->assertRedirect('/pengaduan');
        $this->post('/pengaduan', ['kategori' => 'saran', 'isi' => 'Pesan dari bot otomatis yang tidak diinginkan.', 'situs' => 'http://spam.test']);
        $this->post('/pengaduan', ['kategori' => 'saran', 'isi' => 'pendek'])->assertSessionHasErrors('isi');

        $this->assertSame(1, Pengaduan::count());
        $this->assertSame('baru', Pengaduan::first()->status);
    }

    public function test_guru_aktif_hanya_dihitung_dari_wali_kelas(): void
    {
        $wali = $this->guru('wali@tebakaksi.test');
        $mapel = $this->guru('mapel@tebakaksi.test', 'mapel');
        $kelas = Kelas::create(['tahun_ajaran_id' => $this->ta->id, 'tingkat' => 4, 'nama' => '4A', 'wali_kelas_id' => $wali->id]);
        $mapel->kelasMapel()->attach($kelas->id);

        // Wali mengisi semua hari kegiatan 12–14 Oktober; guru mapel hanya sekali
        foreach (['2026-10-12', '2026-10-13', '2026-10-14'] as $tgl) {
            $k = KegiatanHarian::create(['kelas_id' => $kelas->id, 'tanggal' => $tgl]);
            $k->riwayat()->create(['user_id' => $wali->id, 'aksi' => 'membuat']);
        }
        KegiatanHarian::first()->riwayat()->create(['user_id' => $mapel->id, 'aksi' => 'mengubah']);

        $hasil = Rekap::indikator(Carbon::parse('2026-10-12'), Carbon::parse('2026-10-14'));

        $this->assertSame(100.0, $hasil['guru_aktif']['nilai']);
        $this->assertStringContainsString('1 dari 1 wali kelas', $hasil['guru_aktif']['keterangan']);
    }

    public function test_laporan_baku_bisa_diunduh_per_semester(): void
    {
        $this->actingAs(User::where('peran', 'admin')->first());
        Kelas::create(['tahun_ajaran_id' => $this->ta->id, 'tingkat' => 1, 'nama' => '1A']);

        $this->get('/rekap/unduh?semester=1&laporan=budaya&format=xlsx')->assertOk();
        $this->get('/rekap/unduh?semester=1&laporan=pelanggaran&format=pdf')->assertOk();
        $this->get('/rekap/unduh?laporan=budaya&format=pdf')->assertSessionHasErrors('bulan');
    }
}
