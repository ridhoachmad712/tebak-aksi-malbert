<?php

namespace App\Livewire;

use App\Models\Pengaturan;
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
        // Bagian yang disembunyikan admin (Tampilan Situs → Dashboard Kepsek) tidak dihitung sama sekali
        $tampil = collect(['aspek', 'tindakan', 'pencatatan', 'kabar'])->mapWithKeys(fn ($b) => [$b => Pengaturan::aktif("dasbor_tampil_$b")])->all();
        $periode = Rekap::periode('bulan', $this->bulan, (string) Rekap::semesterDari(today()));
        $monev = $tampil['kabar'] ? RingkasanMonev::susun($periode) : null;

        return view('livewire.dasbor-kepsek', [
            'tampil' => $tampil,
            'ta' => Rekap::tahunAjaranAktif(),
            'label' => $periode['label'],
            'belumBerubah' => $tampil['tindakan'] ? Data::belumBerubah() : collect(),
            'pelanggaranBerat' => $tampil['tindakan'] ? Data::pelanggaranBerat() : collect(),
            'pencatatan' => $tampil['pencatatan'] ? collect(TugasAdmin::pencatatanKelas())->sortBy([['hari_terlewat', 'desc'], ['minggu_ini', 'asc']])->values() : collect(),
            'kelasNaik' => $monev ? Data::kelasNaik($monev['indikator'], $monev['indikatorLalu']) : null,
            'labelLalu' => $periode['sebelumnya']['label'] ?? null,
            'karya' => $tampil['kabar'] ? Data::karyaTerbaru() : collect(),
            'aspekInovasi' => $tampil['aspek'] ? AspekInovasi::bulan($this->bulan) : null,
            'labelBulanAspek' => Rekap::daftarBulan()[$this->bulan] ?? $this->bulan,
        ]);
    }
}
