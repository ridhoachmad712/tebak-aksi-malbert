<?php

namespace App\Livewire\Guru;

use App\Livewire\Guru\Concerns\PilihKelas;
use App\Services\Rekap;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.guru')]
#[Title('Rekap Bulanan')]
class RekapKelas extends Component
{
    use PilihKelas;

    public const LAPORAN = [
        'budaya' => 'Budaya Membaca',
        'literasi' => 'Kemampuan Literasi',
        'pelanggaran' => 'Pelanggaran',
        'ekosistem' => 'Ekosistem Literat',
    ];

    #[Url]
    public string $bulan = '';

    #[Url]
    public string $laporan = 'budaya';

    public function mount(): void
    {
        $this->inisialisasiKelas(request()->integer('kelas') ?: null);
        $this->bulan = preg_match('/^\d{4}-\d{2}$/', $this->bulan) ? $this->bulan : today()->format('Y-m');
        $this->laporan = array_key_exists($this->laporan, self::LAPORAN) ? $this->laporan : 'budaya';
    }

    public function render()
    {
        $data = ['daftarLaporan' => self::LAPORAN];

        if ($this->kelas) {
            $tgl = Carbon::createFromFormat('Y-m-d', $this->bulan.'-01');
            [$dari, $sampai] = Rekap::periodeBulan($tgl->year, $tgl->month);
            $data['labelBulan'] = Rekap::labelBulan($tgl);
            $data['rekap'] = Rekap::rekapKelas($this->kelas, $dari, $sampai);
            $data['kasus'] = $this->laporan === 'pelanggaran' ? Rekap::kasus($dari, $sampai, $this->kelas) : collect();
            $data['guru'] = $this->laporan === 'ekosistem' ? Rekap::rekapGuru($dari, $sampai, $this->kelas) : null;
        }

        return view('livewire.guru.rekap-kelas', $data);
    }
}
