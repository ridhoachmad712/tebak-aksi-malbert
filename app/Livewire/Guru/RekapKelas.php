<?php

namespace App\Livewire\Guru;

use App\Livewire\Concerns\PilihPeriode;
use App\Livewire\Guru\Concerns\PilihKelas;
use App\Models\KegiatanHarian;
use App\Models\Pengaturan;
use App\Services\Rekap;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.guru')]
#[Title('Rekap')]
class RekapKelas extends Component
{
    use PilihKelas;
    use PilihPeriode;

    public const LAPORAN = [
        'budaya' => 'Budaya Membaca',
        'literasi' => 'Kemampuan Literasi',
        'pelanggaran' => 'Pelanggaran',
        'ekosistem' => 'Ekosistem Literat',
    ];

    /** Nilai yang dipakai untuk urutan, saringan, dan ringkasan pada setiap laporan. */
    public const NILAI_UTAMA = [
        'budaya' => 'budaya_persen',
        'literasi' => 'literasi_persen',
        'ekosistem' => 'hadir_persen',
    ];

    public const URUTAN = [
        'nama' => 'Urut nama',
        'terendah' => 'Nilai terendah dulu',
        'tertinggi' => 'Nilai tertinggi dulu',
    ];

    #[Url]
    public string $laporan = 'budaya';

    #[Url]
    public string $urut = 'nama';

    #[Url(as: 'perhatian')]
    public bool $hanyaPerhatian = false;

    public function mount(): void
    {
        $this->inisialisasiKelas(request()->integer('kelas') ?: null);
        $this->inisialisasiPeriode();
        $this->laporan = array_key_exists($this->laporan, self::LAPORAN) ? $this->laporan : 'budaya';
        $this->urut = array_key_exists($this->urut, self::URUTAN) ? $this->urut : 'nama';
    }

    /** Murid "perlu perhatian" = nilai utamanya di bawah kategori Baik (Cukup atau Perlu Bimbingan). */
    public static function perluPerhatian(?float $persen): bool
    {
        return $persen !== null && $persen < Pengaturan::angka('batas_baik');
    }

    public function render()
    {
        $data = ['daftarLaporan' => self::LAPORAN];

        if ($this->kelas) {
            $periode = $this->dataPeriode();
            $data['labelPeriode'] = $periode['label'];
            $data['unduh'] = $periode['unduh'];
            $data['rekap'] = Rekap::rekapKelas($this->kelas, $periode['dari'], $periode['sampai']);
            $data['kasus'] = $this->laporan === 'pelanggaran' ? Rekap::kasus($periode['dari'], $periode['sampai'], $this->kelas) : collect();
            $data['guru'] = $this->laporan === 'ekosistem' ? Rekap::rekapGuru($periode['dari'], $periode['sampai'], $this->kelas) : null;

            if ($kunci = self::NILAI_UTAMA[$this->laporan] ?? null) {
                $semua = $data['rekap']['murid'];
                $perhatian = $semua->filter(fn ($b) => self::perluPerhatian($b[$kunci]));
                $tampil = $this->hanyaPerhatian ? $perhatian : $semua;
                $tampil = match ($this->urut) {
                    'terendah' => $tampil->sortBy(fn ($b) => $b[$kunci] ?? -1),
                    'tertinggi' => $tampil->sortByDesc(fn ($b) => $b[$kunci] ?? -1),
                    default => $tampil,
                };

                $data['murid'] = $tampil->values();
                $data['ringkasan'] = [
                    'rerata' => Rekap::rerata($semua->pluck($kunci)->all()),
                    'perhatian' => $perhatian->count(),
                    'jumlah' => $semua->count(),
                ];
                // Baris rata-rata kelas untuk setiap kolom persen
                $data['rerata'] = collect(array_keys(KegiatanHarian::INDIKATOR))
                    ->mapWithKeys(fn ($i) => [$i => Rekap::rerata($semua->pluck($i.'_persen')->all())])
                    ->put('budaya', Rekap::rerata($semua->pluck('budaya_persen')->all()))
                    ->put('literasi', Rekap::rerata($semua->pluck('literasi_persen')->all()))
                    ->put('karya', $semua->sum('karya'));
            }
        }

        return view('livewire.guru.rekap-kelas', $data);
    }
}
