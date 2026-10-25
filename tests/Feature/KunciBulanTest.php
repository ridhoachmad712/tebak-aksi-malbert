<?php

namespace Tests\Feature;

use App\Filament\Pages\PengaturanTarget;
use App\Livewire\Guru\Beranda;
use App\Livewire\Guru\KaryaForm;
use App\Livewire\Guru\KegiatanHarianForm;
use App\Livewire\Guru\PembinaanDetail;
use App\Livewire\Guru\PembinaanForm;
use App\Models\JenisKarya;
use App\Models\JenisPelanggaran;
use App\Models\KaryaLiterasi;
use App\Models\KasusPembinaan;
use App\Models\KegiatanHarian;
use App\Models\Kelas;
use App\Models\Murid;
use App\Models\Pengaturan;
use App\Models\TahunAjaran;
use App\Models\TindakanPembinaan;
use App\Models\User;
use Database\Seeders\DataAwalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class KunciBulanTest extends TestCase
{
    use RefreshDatabase;

    protected User $guru;

    protected Kelas $kelas;

    protected Murid $ani;

    protected function setUp(): void
    {
        parent::setUp();
        // Rabu, 14 Oktober 2026
        Carbon::setTestNow('2026-10-14 08:00:00');
        $this->seed(DataAwalSeeder::class);

        $ta = TahunAjaran::create(['nama' => '2026/2027', 'mulai' => '2026-07-13', 'selesai' => '2027-06-30', 'aktif' => true]);
        $this->guru = User::create(['name' => 'Guru Uji', 'email' => 'uji@tebakaksi.test', 'password' => 'password', 'peran' => 'guru', 'jenis_guru' => 'kelas', 'status' => 'aktif']);
        $this->kelas = Kelas::create(['tahun_ajaran_id' => $ta->id, 'tingkat' => 4, 'nama' => '4A', 'wali_kelas_id' => $this->guru->id]);
        $this->ani = Murid::create(['nama' => 'Ani Contoh']);
        $this->kelas->murid()->attach($this->ani->id);
        // Guru boleh mengubah catatan sejak awal tahun ajaran, agar yang diuji hanya kunci bulan
        Pengaturan::simpan(['ubah_mulai_tanggal' => '2026-07-13']);
    }

    protected function kasus(string $tanggal): KasusPembinaan
    {
        return KasusPembinaan::create([
            'murid_id' => $this->ani->id, 'kelas_id' => $this->kelas->id, 'tanggal' => $tanggal,
            'jenis_pelanggaran_id' => JenisPelanggaran::first()->id, 'tingkat' => 'ringan',
            'tindakan_pembinaan_id' => TindakanPembinaan::first()->id,
        ]);
    }

    public function test_admin_mengunci_dan_membuka_bulan(): void
    {
        $this->actingAs(User::where('peran', 'admin')->first());
        Livewire::test(PengaturanTarget::class)
            ->assertSee('Kunci isian per bulan')
            ->set('data.bulan_terkunci', ['2026-09'])
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertSame(['2026-09'], Pengaturan::bulanTerkunci());
        $this->assertTrue(Pengaturan::terkunci('2026-09-30'));
        $this->assertFalse(Pengaturan::terkunci('2026-10-01'));

        Livewire::test(PengaturanTarget::class)->assertSet('data.bulan_terkunci', ['2026-09'])
            ->set('data.bulan_terkunci', [])->call('simpan');
        $this->assertSame([], Pengaturan::bulanTerkunci());
    }

    public function test_kegiatan_harian_bulan_terkunci_hanya_bisa_dilihat_termasuk_oleh_admin(): void
    {
        Pengaturan::simpan(['bulan_terkunci' => ['2026-09']]);

        foreach ([$this->guru, User::where('peran', 'admin')->first()] as $pengguna) {
            Livewire::actingAs($pengguna)->test(KegiatanHarianForm::class, ['kelas' => $this->kelas->id])
                ->set('tanggal', '2026-09-15')
                ->assertSee('Isian September 2026 sudah dikunci admin')
                ->call('simpan', [$this->ani->id => ['hadir' => true]])
                ->assertForbidden();
        }
        $this->assertSame(0, KegiatanHarian::count());

        // Bulan yang tidak dikunci tetap bisa diisi
        Livewire::actingAs($this->guru)->test(KegiatanHarianForm::class, ['kelas' => $this->kelas->id])
            ->set('tanggal', '2026-10-13')
            ->call('simpan', [$this->ani->id => ['hadir' => true]])
            ->assertOk();
        $this->assertSame(1, KegiatanHarian::count());
    }

    public function test_kasus_bulan_terkunci_tidak_bisa_dipantau_diubah_atau_dihapus(): void
    {
        $kasus = $this->kasus('2026-09-10');
        Pengaturan::simpan(['bulan_terkunci' => ['2026-09']]);

        Livewire::actingAs($this->guru)->test(PembinaanDetail::class, ['kasus' => $kasus])
            ->assertSee('Isian September 2026 sudah dikunci admin')
            ->assertDontSee('Ubah data kasus')
            ->assertSet('minggu', null)
            ->call('tetapkanStatus', 'positif')->assertForbidden();
        Livewire::actingAs($this->guru)->test(PembinaanDetail::class, ['kasus' => $kasus])
            ->set('minggu', 1)->call('pantau', 'positif')->assertForbidden();
        Livewire::actingAs($this->guru)->test(PembinaanDetail::class, ['kasus' => $kasus])
            ->call('hapus')->assertForbidden();
        Livewire::actingAs($this->guru)->test(Beranda::class)
            ->call('pantau', $kasus->id, 'positif')->assertForbidden();

        // Halaman ubah dialihkan ke rincian, dan kasus baru tidak bisa bertanggal di bulan terkunci
        Livewire::actingAs($this->guru)->test(PembinaanForm::class, ['kasus' => $kasus])
            ->assertRedirect(route('guru.pembinaan.detail', $kasus));
        Livewire::actingAs($this->guru)->test(PembinaanForm::class)
            ->set('muridId', (string) $this->ani->id)->set('tanggal', '2026-09-20')
            ->set('pelanggaran', (string) JenisPelanggaran::first()->id)->set('tingkat', 'ringan')
            ->set('tindakan', (string) TindakanPembinaan::first()->id)
            ->call('simpan')
            ->assertHasErrors('tanggal');

        $kasus->refresh();
        $this->assertNull($kasus->status_akhir);
        $this->assertSame(0, $kasus->pemantauan()->count());
        $this->assertSame(1, KasusPembinaan::count());

        // Setelah dibuka kembali oleh admin, kasus bisa ditetapkan statusnya
        Pengaturan::simpan(['bulan_terkunci' => []]);
        Livewire::actingAs($this->guru)->test(PembinaanDetail::class, ['kasus' => $kasus])
            ->call('tetapkanStatus', 'positif')->assertOk();
        $this->assertSame('positif', $kasus->fresh()->status_akhir);
    }

    public function test_karya_bulan_terkunci_tidak_bisa_diubah_atau_ditambah(): void
    {
        $karya = KaryaLiterasi::create([
            'kelas_id' => $this->kelas->id, 'tanggal' => '2026-09-12', 'jenis_karya_id' => JenisKarya::first()->id,
            'judul' => 'Puisi Lama', 'user_id' => $this->guru->id,
        ]);
        $karya->murid()->attach($this->ani->id);
        Pengaturan::simpan(['bulan_terkunci' => ['2026-09']]);

        Livewire::actingAs($this->guru)->test(KaryaForm::class, ['karya' => $karya])
            ->assertRedirect(route('guru.karya'));
        Livewire::actingAs($this->guru)->test(KaryaForm::class)
            ->set('muridDipilih', [(string) $this->ani->id])->set('tanggal', '2026-09-25')
            ->set('jenis', (string) JenisKarya::first()->id)->set('judul', 'Karya Baru')
            ->call('simpan')
            ->assertHasErrors('tanggal');
        $this->assertSame(1, KaryaLiterasi::count());
    }
}
