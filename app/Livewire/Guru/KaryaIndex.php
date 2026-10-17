<?php

namespace App\Livewire\Guru;

use App\Models\KaryaLiterasi;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.guru')]
#[Title('Karya Literasi')]
class KaryaIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $kelas = '';

    #[Url]
    public string $cari = '';

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

        $karya = KaryaLiterasi::with(['murid', 'guruPemilik', 'jenisKarya', 'kelas', 'foto'])
            ->where(fn (Builder $q) => $q->whereIn('kelas_id', $idKelas)->orWhere('guru_pemilik_id', auth()->id()))
            ->when($this->cari, fn ($q) => $q->where(fn ($w) => $w->where('judul', 'like', "%{$this->cari}%")
                ->orWhereHas('murid', fn ($m) => $m->where('nama', 'like', "%{$this->cari}%"))))
            ->latest('tanggal')->latest('id')
            ->paginate(12);

        return view('livewire.guru.karya-index', ['daftarKelas' => $daftarKelas, 'karya' => $karya]);
    }
}
