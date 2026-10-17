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
#[Title('Rekap')]
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
    public string $periode = 'bulan'; // bulan | semester

    #[Url]
    public string $bulan = '';

    #[Url]
    public string $semester = '';

    #[Url]
    public string $laporan = 'budaya';

    public function mount(): void
    {
        $this->inisialisasiKelas(request()->integer('kelas') ?: null);
        $this->periode = $this->periode === 'semester' ? 'semester' : 'bulan';
        $this->bulan = preg_match('/^\d{4}-\d{2}$/', $this->bulan) ? $this->bulan : today()->format('Y-m');
        $this->semester = in_array($this->semester, ['1', '2'], true) ? $this->semester : (string) Rekap::semesterDari(today());
        $this->laporan = array_key_exists($this->laporan, self::LAPORAN) ? $this->laporan : 'budaya';
    }

    /** Parameter periode untuk tautan unduhan. */
    public function parameterPeriode(): array
    {
        return $this->periode === 'semester' ? ['semester' => $this->semester] : ['bulan' => $this->bulan];
    }

    public function render()
    {
        $data = ['daftarLaporan' => self::LAPORAN];
        $ta = Rekap::tahunAjaranAktif();

        if ($this->kelas) {
            if ($this->periode === 'semester' && $ta) {
                [$dari, $sampai] = Rekap::periodeSemester($ta, (int) $this->semester);
                $data['labelPeriode'] = Rekap::labelSemester((int) $this->semester, $ta);
            } else {
                $tgl = Carbon::createFromFormat('Y-m-d', $this->bulan.'-01');
                [$dari, $sampai] = Rekap::periodeBulan($tgl->year, $tgl->month);
                $data['labelPeriode'] = Rekap::labelBulan($tgl);
            }
            $data['rekap'] = Rekap::rekapKelas($this->kelas, $dari, $sampai);
            $data['kasus'] = $this->laporan === 'pelanggaran' ? Rekap::kasus($dari, $sampai, $this->kelas) : collect();
            $data['guru'] = $this->laporan === 'ekosistem' ? Rekap::rekapGuru($dari, $sampai, $this->kelas) : null;
        }

        return view('livewire.guru.rekap-kelas', $data);
    }
}
