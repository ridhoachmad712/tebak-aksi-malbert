<?php

namespace App\Livewire\Guru;

use App\Livewire\Guru\Concerns\PilihKelas;
use App\Models\Foto;
use App\Models\JenisKegiatan;
use App\Models\KegiatanHarian;
use App\Models\Pengaturan;
use App\Services\Rekap;
use App\Services\SimpanFoto;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.guru')]
#[Title('Kegiatan Harian')]
class KegiatanHarianForm extends Component
{
    use PilihKelas, WithFileUploads;

    #[Url]
    public string $tanggal = '';

    /** @var array<int, array<string, bool>> centang per murid */
    public array $centang = [];

    public array $jenisDipilih = [];

    public string $catatan = '';

    public array $fotoBaru = [];

    public function mount(?int $kelas = null): void
    {
        $this->inisialisasiKelas($kelas);
        $this->tanggal = $this->normalkanTanggal($this->tanggal);
        $this->muat();
    }

    public function updatedKelasId(): void
    {
        abort_unless($this->daftarKelas->contains('id', $this->kelasId), 403);
        session(['kelas_dipilih' => $this->kelasId]);
        $this->muat();
    }

    public function updatedTanggal(): void
    {
        $this->tanggal = $this->normalkanTanggal($this->tanggal);
        $this->muat();
    }

    public function pindahHari(int $arah): void
    {
        $tgl = Carbon::parse($this->tanggal);
        for ($i = 0; $i < 30; $i++) {
            $tgl->addDays($arah);
            if (Rekap::hariKegiatanIni($tgl)) {
                break;
            }
        }
        $this->tanggal = $this->normalkanTanggal($tgl->toDateString());
        $this->muat();
    }

    protected function normalkanTanggal(?string $tanggal): string
    {
        try {
            $tgl = $tanggal ? Carbon::parse($tanggal) : today();
        } catch (\Throwable) {
            $tgl = today();
        }

        return $tgl->min(today())->toDateString();
    }

    protected function muat(): void
    {
        unset($this->kegiatan, $this->murid, $this->bolehUbah, $this->hariKegiatan);
        $this->centang = [];
        $kosong = array_fill_keys(array_keys(KegiatanHarian::INDIKATOR), false);

        if (! $this->kelas) {
            return;
        }

        $tersimpan = $this->kegiatan?->catatanMurid->keyBy('murid_id') ?? collect();
        foreach ($this->murid as $m) {
            $baris = $tersimpan->get($m->id);
            $this->centang[$m->id] = $baris ? $baris->only(array_keys($kosong)) : $kosong;
        }

        $this->jenisDipilih = $this->kegiatan?->jenisKegiatan->pluck('id')->map(fn ($id) => (string) $id)->all() ?? [];
        $this->catatan = $this->kegiatan?->catatan ?? '';
    }

    #[Computed]
    public function kegiatan(): ?KegiatanHarian
    {
        return $this->kelasId
            ? KegiatanHarian::with(['catatanMurid', 'jenisKegiatan', 'foto', 'riwayat.user'])
                ->where('kelas_id', $this->kelasId)->whereDate('tanggal', $this->tanggal)->first()
            : null;
    }

    #[Computed]
    public function murid()
    {
        return $this->kelas?->muridAktif()->get() ?? collect();
    }

    #[Computed]
    public function hariKegiatan(): bool
    {
        return Rekap::hariKegiatanIni(Carbon::parse($this->tanggal));
    }

    /** Guru hanya boleh mengubah catatan dalam batas hari yang diatur admin; admin kapan saja. */
    #[Computed]
    public function bolehUbah(): bool
    {
        $tgl = Carbon::parse($this->tanggal);

        if (! $this->kelas || $tgl->isFuture() || ! $this->hariKegiatan) {
            return false;
        }

        return auth()->user()->isAdmin()
            || $tgl->gte(today()->subDays((int) Pengaturan::angka('batas_ubah_hari')));
    }

    protected function pastikanBolehUbah(): void
    {
        abort_unless($this->bolehUbah && $this->daftarKelas->contains('id', $this->kelasId), 403);
    }

    protected function kegiatanAtauBuat(): KegiatanHarian
    {
        $kegiatan = KegiatanHarian::firstOrCreate(
            ['kelas_id' => $this->kelasId, 'tanggal' => $this->tanggal],
            ['dibuat_oleh' => auth()->id(), 'diubah_oleh' => auth()->id()],
        );
        unset($this->kegiatan);

        return $kegiatan;
    }

    /** Menyimpan centang murid yang berubah. Dipanggil dari tabel (Alpine) tanpa render ulang. */
    #[Renderless]
    public function simpan(array $perubahan): array
    {
        $this->pastikanBolehUbah();

        $idMurid = $this->murid->pluck('id')->flip();
        $kolom = array_keys(KegiatanHarian::INDIKATOR);
        $kegiatan = $this->kegiatanAtauBuat();

        foreach ($perubahan as $muridId => $nilai) {
            if (! $idMurid->has($muridId) || ! is_array($nilai)) {
                continue;
            }

            $baris = [];
            foreach ($kolom as $k) {
                $baris[$k] = (bool) ($nilai[$k] ?? false);
            }
            // Tidak hadir berarti indikator lain tidak terpenuhi
            if (! $baris['hadir']) {
                $baris = array_fill_keys($kolom, false);
            }

            $kegiatan->catatanMurid()->updateOrCreate(['murid_id' => $muridId], $baris);
            $this->centang[$muridId] = $baris;
        }

        $this->catatPerubahan($kegiatan);

        return ['jam' => now()->format('H.i')];
    }

    public function toggleJenis(int $id): void
    {
        $this->pastikanBolehUbah();
        abort_unless(JenisKegiatan::whereKey($id)->exists(), 404);

        $kegiatan = $this->kegiatanAtauBuat();
        $kegiatan->jenisKegiatan()->toggle($id);
        $this->catatPerubahan($kegiatan);
        $this->jenisDipilih = $kegiatan->jenisKegiatan()->pluck('jenis_kegiatan.id')->map(fn ($v) => (string) $v)->all();
    }

    public function simpanCatatan(): void
    {
        $this->pastikanBolehUbah();
        $this->validate(['catatan' => ['nullable', 'string', 'max:2000']]);

        $kegiatan = $this->kegiatanAtauBuat();
        $kegiatan->update(['catatan' => $this->catatan ?: null]);
        $this->catatPerubahan($kegiatan);
        $this->dispatch('notifikasi', pesan: 'Catatan disimpan');
    }

    public function updatedFotoBaru(): void
    {
        $this->pastikanBolehUbah();
        $this->validate(['fotoBaru.*' => ['image', 'max:'.SimpanFoto::MAKS_KB]], [], ['fotoBaru.*' => 'foto']);

        $kegiatan = $this->kegiatanAtauBuat();
        foreach ($this->fotoBaru as $file) {
            $kegiatan->foto()->create([
                'path' => SimpanFoto::simpan($file, 'kegiatan'),
                'user_id' => auth()->id(),
            ]);
        }
        $this->fotoBaru = [];
        $this->catatPerubahan($kegiatan);
        unset($this->kegiatan);
        $this->dispatch('notifikasi', pesan: 'Foto dokumentasi ditambahkan');
    }

    public function hapusFoto(int $id): void
    {
        $this->pastikanBolehUbah();
        $this->kegiatan?->foto()->whereKey($id)->first()?->delete();
        unset($this->kegiatan);
    }

    /** Jejak pengisian: dicatat sekali per guru per 30 menit agar riwayat tetap ringkas. */
    protected function catatPerubahan(KegiatanHarian $kegiatan): void
    {
        $kegiatan->update(['diubah_oleh' => auth()->id()]);
        $terakhir = $kegiatan->riwayat()->first();

        if ($terakhir && $terakhir->user_id === auth()->id() && $terakhir->created_at->gt(now()->subMinutes(30))) {
            return;
        }

        $kegiatan->riwayat()->create([
            'user_id' => auth()->id(),
            'aksi' => $terakhir ? 'mengubah' : 'membuat',
        ]);
    }

    public function render()
    {
        return view('livewire.guru.kegiatan-harian', [
            'jenisKegiatan' => JenisKegiatan::aktif()->urut()->get(),
            'indikator' => KegiatanHarian::INDIKATOR,
            'tgl' => Carbon::parse($this->tanggal),
        ]);
    }
}
