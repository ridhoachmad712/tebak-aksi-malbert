<?php

namespace App\Livewire;

use App\Services\AspekInovasi;
use App\Services\DasborKepsek as Data;
use App\Services\Rekap;
use App\Services\RingkasanMonev;
use App\Services\TugasAdmin;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/** Dashboard kepala sekolah: yang perlu diketahui dan ditindak minggu ini. Evaluasi lengkap ada di Monev. */
#[Layout('components.layouts.guru')]
#[Title('Dashboard')]
class DasborKepsek extends Component
{
    /** Bulan yang ditampilkan; bawaannya bulan lalu selama bulan berjalan belum selesai. */
    #[Url]
    public string $bulan = '';

    public function mount(): void
    {
        $daftar = Rekap::daftarBulan();
        $this->bulan = array_key_exists($this->bulan, $daftar) ? $this->bulan : Rekap::bulanBawaan();
    }

    public function render()
    {
        $periode = Rekap::periode('bulan', $this->bulan, (string) Rekap::semesterDari(today()));
        $monev = RingkasanMonev::susun($periode);
        $indikator = $monev['indikator'];

        return view('livewire.dasbor-kepsek', [
            'ta' => Rekap::tahunAjaranAktif(),
            'label' => $periode['label'],
            'belumBerubah' => Data::belumBerubah(),
            'pelanggaranBerat' => Data::pelanggaranBerat(),
            'pencatatan' => collect(TugasAdmin::pencatatanKelas())->sortBy([['hari_terlewat', 'desc'], ['minggu_ini', 'asc']])->values(),
            'kelasNaik' => Data::kelasNaik($indikator, $monev['indikatorLalu']),
            'labelLalu' => $periode['sebelumnya']['label'] ?? null,
            'karya' => Data::karyaTerbaru(),
            'aspekInovasi' => AspekInovasi::bulan($this->bulan),
            'labelBulanAspek' => Rekap::daftarBulan()[$this->bulan] ?? $this->bulan,
        ]);
    }
}
