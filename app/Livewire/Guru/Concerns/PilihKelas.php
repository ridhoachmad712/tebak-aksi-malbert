<?php

namespace App\Livewire\Guru\Concerns;

use App\Models\Kelas;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;

/** Pilihan kelas untuk guru yang memegang lebih dari satu kelas; diingat di sesi. */
trait PilihKelas
{
    public ?int $kelasId = null;

    public function inisialisasiKelas(?int $kelasId = null): void
    {
        $ids = $this->daftarKelas->pluck('id');
        $kandidat = $kelasId ?? session('kelas_dipilih');

        $this->kelasId = $ids->contains($kandidat) ? (int) $kandidat : $ids->first();
        session(['kelas_dipilih' => $this->kelasId]);
    }

    public function updatedKelasId(): void
    {
        abort_unless($this->daftarKelas->contains('id', $this->kelasId), 403);
        session(['kelas_dipilih' => $this->kelasId]);
    }

    /** @return Collection<int, Kelas> */
    #[Computed]
    public function daftarKelas(): Collection
    {
        return auth()->user()->kelasAkses();
    }

    #[Computed]
    public function kelas(): ?Kelas
    {
        return $this->daftarKelas->firstWhere('id', $this->kelasId);
    }
}
