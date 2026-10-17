<?php

namespace App\Livewire;

use App\Models\Kelas;
use App\Services\Rekap;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.guru')]
#[Title('Monev')]
class Monev extends Component
{
    #[Url]
    public string $periode = 'bulan'; // bulan | semester

    #[Url]
    public string $bulan = '';

    #[Url]
    public string $semester = '';

    #[Url]
    public string $kelas = '';

    public function mount(): void
    {
        $this->periode = $this->periode === 'semester' ? 'semester' : 'bulan';
        $this->bulan = preg_match('/^\d{4}-\d{2}$/', $this->bulan) ? $this->bulan : today()->format('Y-m');
        $this->semester = in_array($this->semester, ['1', '2'], true) ? $this->semester : (string) Rekap::semesterDari(today());
    }

    public function render()
    {
        $ta = Rekap::tahunAjaranAktif();
        $kelasFilter = $this->kelas ? Kelas::aktif()->find($this->kelas) : null;

        if ($this->periode === 'semester' && $ta) {
            [$dari, $sampai] = Rekap::periodeSemester($ta, (int) $this->semester);
            $label = Rekap::labelSemester((int) $this->semester, $ta);
            $unduh = ['semester' => $this->semester];
        } else {
            $tgl = Carbon::createFromFormat('Y-m-d', $this->bulan.'-01');
            [$dari, $sampai] = Rekap::periodeBulan($tgl->year, $tgl->month);
            $label = Rekap::labelBulan($tgl);
            $unduh = ['bulan' => $this->bulan];
        }

        return view('livewire.monev', [
            'ta' => $ta,
            'label' => $label,
            'unduh' => $unduh + ($kelasFilter ? ['kelas' => $kelasFilter->id] : []),
            'daftarKelas' => Kelas::aktif()->orderBy('tingkat')->orderBy('nama')->get(),
            'indikator' => Rekap::indikator($dari, $sampai, $kelasFilter),
            'tren' => Rekap::trenBulanan($kelasFilter),
            'hari' => Rekap::hariKegiatan($dari, $sampai)->count(),
        ]);
    }
}
