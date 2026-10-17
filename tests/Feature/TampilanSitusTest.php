<?php

namespace Tests\Feature;

use App\Filament\Pages\TampilanSitus;
use App\Models\Konten;
use App\Models\Pengaturan;
use App\Models\Pengumuman;
use App\Models\TahunAjaran;
use App\Models\User;
use Database\Seeders\DataAwalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class TampilanSitusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-14 08:00:00');
        $this->seed(DataAwalSeeder::class);
        TahunAjaran::create(['nama' => '2026/2027', 'mulai' => '2026-07-13', 'selesai' => '2027-06-30', 'aktif' => true]);
        Konten::create(['jenis' => 'halaman', 'judul' => 'Tentang TEBAK AKSI', 'slug' => 'tentang-tebak-aksi', 'terbit' => true, 'isi' => '<p>Isi</p>']);
    }

    public function test_halaman_depan_memakai_nilai_bawaan(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Lima belas menit membaca, setiap hari, di teras kelas.')
            ->assertSee('Arti nama TEBAK AKSI')
            ->assertSee('href="'.route('halaman', 'tentang-tebak-aksi').'"', false);
    }

    public function test_admin_mengubah_teks_bagian_menu_dan_media_sosial(): void
    {
        $this->actingAs(User::where('peran', 'admin')->first());

        Livewire::test(TampilanSitus::class)
            ->set('data.beranda_judul', 'Membaca bersama setiap pagi.')
            ->set('data.beranda_tampil_angka', false)
            ->set('data.alur_judul', 'Langkah Teras Baca kami')
            ->set('data.alur_langkah', [
                ['judul' => 'Membaca', 'keterangan' => 'Murid membaca buku pilihannya.'],
                ['judul' => 'Bercerita', 'keterangan' => 'Murid menceritakan isi buku.'],
            ])
            ->set('data.menu_publik', [
                ['tujuan' => 'beranda', 'label' => 'Beranda', 'tampil' => true],
                ['tujuan' => 'galeri', 'label' => 'Foto Kegiatan', 'tampil' => true],
                ['tujuan' => 'diseminasi', 'label' => 'Diseminasi', 'tampil' => false],
            ])
            ->set('data.medsos_whatsapp', '0812-3456-7890')
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertSame('Membaca bersama setiap pagi.', Pengaturan::ambil('beranda_judul'));
        $this->assertCount(2, Pengaturan::daftar('alur_langkah'));

        auth()->logout();
        $this->get('/')
            ->assertSee('Membaca bersama setiap pagi.')
            ->assertSee('Langkah Teras Baca kami')
            ->assertSee('Bercerita')
            ->assertDontSee('dalam angka')
            ->assertSee('Foto Kegiatan')
            ->assertDontSee('>Diseminasi</a>', false)
            ->assertSee('https://wa.me/6281234567890', false);
    }

    public function test_menu_ke_halaman_yang_dihapus_tidak_ditampilkan(): void
    {
        Konten::where('slug', 'tentang-tebak-aksi')->delete();

        $this->get('/')->assertOk()->assertDontSee('/halaman/tentang-tebak-aksi', false);
    }

    public function test_pengumuman_tampil_sesuai_tanggal_dan_sasaran(): void
    {
        Pengumuman::create(['isi' => 'Teras Baca libur besok.', 'sasaran' => 'publik', 'mulai' => '2026-10-10', 'selesai' => '2026-10-20']);
        Pengumuman::create(['isi' => 'Rapat guru hari Jumat.', 'sasaran' => 'guru', 'mulai' => '2026-10-10']);
        Pengumuman::create(['isi' => 'Pengumuman yang sudah lewat.', 'sasaran' => 'semua', 'mulai' => '2026-09-01', 'selesai' => '2026-09-30']);
        Pengumuman::create(['isi' => 'Pengumuman nonaktif.', 'sasaran' => 'semua', 'mulai' => '2026-10-01', 'aktif' => false]);

        $this->get('/')
            ->assertSee('Teras Baca libur besok.')
            ->assertDontSee('Rapat guru hari Jumat.')
            ->assertDontSee('Pengumuman yang sudah lewat.')
            ->assertDontSee('Pengumuman nonaktif.');

        $guru = User::create(['name' => 'Guru', 'email' => 'g@tebakaksi.test', 'password' => 'password', 'peran' => 'guru', 'jenis_guru' => 'kelas', 'status' => 'aktif']);
        $this->actingAs($guru)->get('/guru')
            ->assertSee('Rapat guru hari Jumat.')
            ->assertDontSee('Teras Baca libur besok.');
    }

    public function test_halaman_admin_tampilan_dan_pengumuman_terbuka(): void
    {
        $this->actingAs(User::where('peran', 'admin')->first());

        $this->get('/admin/tampilan-situs')->assertOk();
        $this->get('/admin/pengumuman')->assertOk();
        $this->get('/admin/pengaturan')->assertOk();
    }

    public function test_nama_kepala_sekolah_diisi_di_identitas_dan_tampil_di_laporan(): void
    {
        $this->actingAs(User::where('peran', 'admin')->first());

        // Langkah persiapan mengarah ke halaman yang memang punya isian nama kepala sekolah
        $langkah = collect(\App\Services\TugasAdmin::persiapan())->firstWhere('label', 'Nama kepala sekolah untuk laporan cetak sudah diisi');
        $this->assertSame(TampilanSitus::getUrl(), $langkah['url']);
        $this->assertFalse($langkah['selesai']);

        Livewire::test(TampilanSitus::class)
            ->assertSee('Nama kepala sekolah')
            ->set('data.nama_kepala_sekolah', 'Kepala Sekolah Uji, S.Pd.')
            ->set('data.nip_kepala_sekolah', '197001011990031001')
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertSame('Kepala Sekolah Uji, S.Pd.', Pengaturan::ambil('nama_kepala_sekolah'));
        $this->assertTrue(collect(\App\Services\TugasAdmin::persiapan())->firstWhere('label', 'Nama kepala sekolah untuk laporan cetak sudah diisi')['selesai']);
        $this->assertStringContainsString('Kepala Sekolah Uji, S.Pd.', view('cetak.monev', \App\Services\RingkasanMonev::susun(\App\Services\Rekap::periode('bulan', '2026-10', '1')) + ['label' => 'Oktober 2026', 'labelLalu' => null])->render());
    }
}
