<?php

namespace Tests\Feature;

use App\Models\KegiatanHarian;
use App\Models\Kelas;
use App\Models\Murid;
use App\Models\Pengaduan;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\TugasAdmin;
use Database\Seeders\DataAwalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DasborAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Kamis, 15 Oktober 2026
        Carbon::setTestNow('2026-10-15 09:00:00');
        $this->seed(DataAwalSeeder::class);
        $this->actingAs(User::where('peran', 'admin')->first());
    }

    public function test_sistem_baru_menampilkan_persiapan_dan_tugas(): void
    {
        User::create(['name' => 'Ibu Sari', 'email' => 'sari@tebakaksi.test', 'password' => 'password', 'peran' => 'guru', 'jenis_guru' => 'kelas', 'status' => 'menunggu']);
        Pengaduan::create(['kategori' => 'saran', 'isi' => 'Mohon tambah buku bergambar untuk kelas satu.']);

        $this->get('/admin')->assertOk()
            ->assertSee('Persiapan sistem')
            ->assertSee('Tahun ajaran aktif sudah diatur')
            ->assertSee('1 pendaftar guru menunggu persetujuan')
            ->assertSee('Ibu Sari')
            ->assertSee('1 pengaduan baru belum ditindaklanjuti')
            ->assertSee('Cadangan data belum pernah diunduh')
            ->assertSee('Pencatatan kelas minggu ini')
            ->assertSee('Lihat capaian program di Monev');
    }

    public function test_pencatatan_kelas_dan_kelas_tertinggal(): void
    {
        $ta = TahunAjaran::create(['nama' => '2026/2027', 'mulai' => '2026-07-13', 'selesai' => '2027-06-30', 'aktif' => true]);
        $wali = User::create(['name' => 'Pak Danu', 'email' => 'danu@tebakaksi.test', 'password' => 'password', 'peran' => 'guru', 'jenis_guru' => 'kelas', 'status' => 'aktif']);
        $rajin = Kelas::create(['tahun_ajaran_id' => $ta->id, 'tingkat' => 1, 'nama' => '1A', 'wali_kelas_id' => $wali->id]);
        $lalai = Kelas::create(['tahun_ajaran_id' => $ta->id, 'tingkat' => 2, 'nama' => '2A']);
        foreach ([$rajin, $lalai] as $k) {
            $k->murid()->attach(Murid::create(['nama' => 'Murid '.$k->nama])->id);
        }
        foreach (['2026-10-12', '2026-10-13', '2026-10-14', '2026-10-15'] as $tgl) {
            KegiatanHarian::create(['kelas_id' => $rajin->id, 'tanggal' => $tgl]);
        }
        KegiatanHarian::create(['kelas_id' => $lalai->id, 'tanggal' => '2026-10-07']);

        $data = collect(TugasAdmin::pencatatanKelas())->keyBy('kelas');
        $this->assertSame(4, $data['1A']['minggu_ini']);
        $this->assertSame(4, $data['1A']['hari_minggu_ini']);
        $this->assertSame(0, $data['1A']['hari_terlewat']);
        $this->assertSame(0, $data['2A']['minggu_ini']);
        $this->assertSame(5, $data['2A']['hari_terlewat']);

        $this->get('/admin')->assertOk()
            ->assertSee('1 kelas belum punya wali kelas')
            ->assertSee('kelas belum mencatat kegiatan')
            ->assertSee('4/4 hari')
            ->assertSee('8 hari lalu');
    }

    public function test_admin_memilih_halaman_dan_bisa_pindah(): void
    {
        $this->get('/arah')->assertRedirect(route('admin.pilih'));
        $this->get('/pilih-halaman')->assertOk()
            ->assertSee('Mau membuka halaman apa?')
            ->assertSee('Halaman Admin')->assertSee('Halaman Monev')->assertSee('Halaman Guru');

        // Di Monev dan halaman guru ada bilah pindah halaman, termasuk kembali ke Halaman Admin
        foreach (['/monev', '/guru'] as $url) {
            $this->get($url)->assertOk()->assertSee('Pindah ke:')->assertSee('href="'.url('/admin').'"', false);
        }
        $this->get('/admin')->assertSee('Halaman Monev')->assertSee('Halaman Guru');

        // Guru dan kepala sekolah tidak melihat halaman pilihan maupun bilahnya
        $guru = User::create(['name' => 'Guru Uji', 'email' => 'uji@tebakaksi.test', 'password' => 'password', 'peran' => 'guru', 'jenis_guru' => 'kelas', 'status' => 'aktif']);
        $this->actingAs($guru)->get('/pilih-halaman')->assertForbidden();
        $this->get('/guru')->assertDontSee('Pindah ke:');
    }
}
