<?php

namespace App\Livewire\Guru;

use App\Models\KasusPembinaan;
use App\Services\Rekap;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.guru')]
#[Title('Kasus Pembinaan')]
class PembinaanIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $tab = 'aktif';

    #[Url]
    public string $kelas = '';

    #[Url]
    public string $cari = '';

    /** Bulan kejadian (Y-m); kosong berarti semua bulan. */
    #[Url]
    public string $bulan = '';

    public function updated(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $daftarKelas = auth()->user()->kelasAkses();
        $idKelas = $this->kelas && $daftarKelas->contains('id', (int) $this->kelas)
            ? [(int) $this->kelas]
            : $daftarKelas->pluck('id')->all();

        $daftarBulan = Rekap::daftarBulan();
        $bulan = array_key_exists($this->bulan, $daftarBulan) ? Carbon::createFromFormat('!Y-m', $this->bulan) : null;

        $dasar = KasusPembinaan::with(['murid', 'kelas', 'jenisPelanggaran', 'tindakanPembinaan', 'pemantauan'])
            ->whereIn('kelas_id', $idKelas)
            ->when($this->cari, fn ($q) => $q->whereHas('murid', fn ($m) => $m->where('nama', 'like', "%{$this->cari}%")))
            ->when($bulan, fn ($q) => $q->whereBetween('tanggal', [$bulan->toDateString(), $bulan->copy()->endOfMonth()->toDateString()]));

        $aktif = (clone $dasar)->dalamPembinaan()->latest('tanggal')->get();
        $perlu = $aktif->filter(fn ($k) => $k->perluDipantau() || $k->siapDitutup())->values();

        $kasus = match ($this->tab) {
            'perlu' => $perlu,
            'selesai' => (clone $dasar)->whereNotNull('status_akhir')->latest('tanggal')->paginate(15),
            default => $aktif,
        };

        return view('livewire.guru.pembinaan-index', [
            'daftarKelas' => $daftarKelas,
            'daftarBulan' => $daftarBulan,
            // Kasus dikelompokkan per bulan kejadian agar mudah dipisahkan
            'perBulan' => collect($kasus instanceof Paginator ? $kasus->items() : $kasus)
                ->groupBy(fn ($k) => $k->tanggal->format('Y-m')),
            'kasus' => $kasus,
            'jumlah' => ['perlu' => $perlu->count(), 'aktif' => $aktif->count()],
        ]);
    }
}
