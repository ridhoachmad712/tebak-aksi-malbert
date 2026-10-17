<?php

namespace App\Livewire\Concerns;

use App\Services\Rekap;
use Livewire\Attributes\Url;

/** Pilihan periode (per bulan atau per semester) yang dipakai Monev dan Rekap. */
trait PilihPeriode
{
    #[Url]
    public string $periode = 'bulan'; // bulan | semester

    #[Url]
    public string $bulan = '';

    #[Url]
    public string $semester = '';

    protected function inisialisasiPeriode(): void
    {
        $this->periode = $this->periode === 'semester' ? 'semester' : 'bulan';
        $daftar = Rekap::daftarBulan();
        $this->bulan = array_key_exists($this->bulan, $daftar) ? $this->bulan : array_key_first($daftar);
        $this->semester = in_array($this->semester, ['1', '2'], true) ? $this->semester : (string) Rekap::semesterDari(today());
    }

    /** Rentang tanggal, label, dan periode pembanding untuk pilihan saat ini. */
    public function dataPeriode(): array
    {
        return Rekap::ingat("periode.{$this->periode}.{$this->bulan}.{$this->semester}", fn () => Rekap::periode($this->periode, $this->bulan, $this->semester));
    }

    /** Parameter periode untuk tautan ke halaman lain dan unduhan. */
    public function parameterPeriode(): array
    {
        return $this->periode === 'semester' ? ['periode' => 'semester', 'semester' => $this->semester] : ['bulan' => $this->bulan];
    }
}
