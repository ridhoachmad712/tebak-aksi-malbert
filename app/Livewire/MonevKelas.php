<?php

namespace App\Livewire;

use App\Livewire\Concerns\PilihPeriode;
use App\Models\KasusPembinaan;
use App\Models\Kelas;
use App\Services\Rekap;
use App\Services\RingkasanMonev;
use Livewire\Attributes\Layout;
use Livewire\Component;

/** Rincian satu kelas untuk kepala sekolah: Monev → Kelas → Murid. */
#[Layout('components.layouts.guru')]
class MonevKelas extends Component
{
    use PilihPeriode;

    public Kelas $kelas;

    public function mount(Kelas $kelas): void
    {
        $this->kelas = $kelas->load('waliKelas');
        $this->inisialisasiPeriode();
    }

    public function render()
    {
        $periode = $this->dataPeriode();
        $data = RingkasanMonev::susun($periode, $this->kelas);

        return view('livewire.monev-kelas', $data + [
            'label' => $periode['label'],
            'labelLalu' => $periode['sebelumnya']['label'] ?? null,
            'unduh' => $periode['unduh'] + ['kelas' => $this->kelas->id],
            'ringkasKelas' => $data['indikator']['per_kelas'][0],
            'muridPerhatian' => RingkasanMonev::muridPerluPerhatian(Rekap::rekapKelas($this->kelas, $periode['dari'], $periode['sampai'])),
            'kasusAktif' => KasusPembinaan::dalamPembinaan()->where('kelas_id', $this->kelas->id)
                ->with(['murid', 'kelas', 'jenisPelanggaran', 'tindakanPembinaan'])->orderBy('tanggal')->get(),
        ])->title('Monev Kelas '.$this->kelas->nama);
    }
}
