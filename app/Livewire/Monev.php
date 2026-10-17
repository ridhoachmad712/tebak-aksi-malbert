<?php

namespace App\Livewire;

use App\Livewire\Concerns\PilihPeriode;
use App\Services\Rekap;
use App\Services\RingkasanMonev;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.guru')]
#[Title('Monev')]
class Monev extends Component
{
    use PilihPeriode;

    public function mount()
    {
        $this->inisialisasiPeriode();

        // Tautan lama ?kelas= kini dibuka di halaman rincian kelas
        if ($kelas = request()->integer('kelas')) {
            return $this->redirectRoute('monev.kelas', ['kelas' => $kelas] + $this->parameterPeriode(), navigate: true);
        }
    }

    public function render()
    {
        $periode = $this->dataPeriode();

        return view('livewire.monev', RingkasanMonev::susun($periode) + [
            'ta' => Rekap::tahunAjaranAktif(),
            'label' => $periode['label'],
            'labelLalu' => $periode['sebelumnya']['label'] ?? null,
            'unduh' => $periode['unduh'],
            'tren' => Rekap::trenBulanan(),
        ]);
    }
}
