<?php

namespace App\Livewire;

use App\Services\DasborKepsek as Data;
use App\Services\Rekap;
use App\Services\RingkasanMonev;
use App\Services\TugasAdmin;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/** Dashboard kepala sekolah: yang perlu diketahui dan ditindak minggu ini. Evaluasi lengkap ada di Monev. */
#[Layout('components.layouts.guru')]
#[Title('Dashboard')]
class DasborKepsek extends Component
{
    public function render()
    {
        $periode = Rekap::periode('bulan', today()->format('Y-m'), (string) Rekap::semesterDari(today()));
        $monev = RingkasanMonev::susun($periode);
        $indikator = $monev['indikator'];

        return view('livewire.dasbor-kepsek', [
            'ta' => Rekap::tahunAjaranAktif(),
            'label' => $periode['label'],
            'kalimat' => $monev['kalimat'][0],
            'angka' => collect($monev['pilar'])->flatten(1)->keyBy('kunci')->only(['murid_aktif', 'budaya_membaca', 'kemampuan_literasi', 'guru_aktif']),
            'belumBerubah' => Data::belumBerubah(),
            'pelanggaranBerat' => Data::pelanggaranBerat(),
            'pencatatan' => collect(TugasAdmin::pencatatanKelas())->sortBy([['hari_terlewat', 'desc'], ['minggu_ini', 'asc']])->values(),
            'kelasPerhatian' => Data::kelasPerhatian($indikator),
            'kelasNaik' => Data::kelasNaik($indikator, $monev['indikatorLalu']),
            'labelLalu' => $periode['sebelumnya']['label'] ?? null,
            'karya' => Data::karyaTerbaru(),
        ]);
    }
}
