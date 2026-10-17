<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\Pengaturan;
use Illuminate\Support\Collection;

/**
 * Menyusun indikator Monev menjadi bentuk yang mudah dibaca kepala sekolah:
 * dikelompokkan per pilar program, dengan selisih dari target, perbandingan periode lalu,
 * dan kalimat kesimpulan.
 */
class RingkasanMonev
{
    public const PILAR = [
        'Budaya Membaca' => [
            'murid_aktif' => 'Murid aktif memanfaatkan Teras Baca',
            'budaya_membaca' => 'Rata-rata Budaya Membaca',
        ],
        'Kemampuan Literasi' => [
            'kemampuan_literasi' => 'Rata-rata Kemampuan Literasi',
        ],
        'Karakter' => [
            'keberhasilan_pembinaan' => 'Keberhasilan pembinaan',
            'penurunan_pelanggaran' => 'Penurunan pelanggaran per semester',
        ],
        'Pelaksanaan' => [
            'guru_aktif' => 'Wali kelas aktif mengisi kegiatan harian',
        ],
    ];

    /** Indikator yang tidak dibandingkan per bulan karena dihitung per semester. */
    protected const TANPA_TREN = ['penurunan_pelanggaran'];

    /** Selisih di bawah target yang masih dianggap "hampir tercapai" (warna kuning, bukan merah). */
    public const SELISIH_RINGAN = 10;

    /**
     * @return array<string, list<array{kunci: string, judul: string, nilai: ?float, target: float, status: string, selisih: ?float, tren: ?float, keterangan: string}>>
     */
    public static function pilar(array $indikator, ?array $indikatorLalu, bool $tingkatKelas = false): array
    {
        $hasil = [];
        foreach (self::PILAR as $pilar => $daftar) {
            foreach ($daftar as $kunci => $judul) {
                // Penurunan pelanggaran hanya bermakna untuk seluruh sekolah
                if ($tingkatKelas && $kunci === 'penurunan_pelanggaran') {
                    continue;
                }
                $d = $indikator[$kunci];
                $nilai = $d['nilai'];
                $cukup = $d['cukup'] ?? true;
                $lalu = $indikatorLalu[$kunci]['nilai'] ?? null;

                $status = match (true) {
                    $nilai === null => 'tanpa_data',
                    ! $cukup => 'belum_cukup',
                    $nilai >= $d['target'] => 'tercapai',
                    default => 'kurang',
                };

                $hasil[$pilar][] = [
                    'kunci' => $kunci,
                    'judul' => $judul,
                    'nilai' => $nilai,
                    'target' => $d['target'],
                    'status' => $status,
                    'selisih' => $status === 'kurang' ? round($d['target'] - $nilai, 1) : null,
                    'tren' => ($nilai !== null && $lalu !== null && $cukup && ! in_array($kunci, self::TANPA_TREN, true)) ? round($nilai - $lalu, 1) : null,
                    'keterangan' => $d['keterangan'],
                ];
            }
        }

        return array_filter($hasil);
    }

    /** Kalimat kesimpulan untuk bagian paling atas Monev. */
    public static function kalimat(array $pilar, array $indikator, string $label, ?string $labelLalu, ?string $namaKelas = null): array
    {
        $semua = collect($pilar)->flatten(1);
        $dinilai = $semua->whereIn('status', ['tercapai', 'kurang']);
        $kalimat = [];

        $kalimat[] = $dinilai->isEmpty()
            ? "{$label}: belum ada data yang cukup untuk dinilai."
            : "{$label}: ".$dinilai->where('status', 'tercapai')->count().' dari '.$dinilai->count().' target tercapai.';

        if ($labelLalu) {
            $berubah = $semua->filter(fn ($b) => $b['tren'] !== null && abs($b['tren']) >= 1);
            $naik = $berubah->sortByDesc('tren')->first();
            $turun = $berubah->sortBy('tren')->first();
            $bagian = [];
            if ($naik && $naik['tren'] > 0) {
                $bagian[] = lcfirst($naik['judul']).' naik '.Rekap::persenTeks($naik['tren'], false).' poin';
            }
            if ($turun && $turun['tren'] < 0) {
                $bagian[] = lcfirst($turun['judul']).' turun '.Rekap::persenTeks(abs($turun['tren']), false).' poin';
            }
            if ($bagian) {
                $kalimat[] = 'Dibanding '.$labelLalu.', '.implode(', sedangkan ', $bagian).'.';
            }
        }

        $perhatian = [];
        if (! $namaKelas) {
            foreach (collect($indikator['kelas_di_bawah_target'] ?? [])->sortBy('persen')->take(2) as $k) {
                $perhatian[] = 'Kelas '.$k['kelas']->nama.' (murid aktif '.Rekap::persenTeks($k['persen']).')';
            }
        }
        $terjauh = $semua->where('status', 'kurang')->sortByDesc('selisih')->first();
        if ($terjauh) {
            $perhatian[] = lcfirst($terjauh['judul']).($namaKelas ? '' : ' seluruh sekolah').' ('.Rekap::persenTeks($terjauh['nilai']).', target '.Rekap::persenTeks($terjauh['target']).')';
        }
        if ($perhatian) {
            $kalimat[] = 'Perhatian utama: '.self::gabung($perhatian).'.';
        }

        return $kalimat;
    }

    /**
     * Satu aturan warna untuk seluruh tampilan kepala sekolah:
     * hanya nilai di bawah target yang diberi warna (kuning bila hampir, merah bila jauh).
     */
    public static function warna(?float $nilai, float $target): ?string
    {
        if ($nilai === null || $nilai >= $target) {
            return null;
        }

        return $target - $nilai <= self::SELISIH_RINGAN ? 'amber' : 'rose';
    }

    /** Data lengkap Monev untuk satu periode: indikator, pilar, dan kalimat ringkasan. */
    public static function susun(array $periode, ?Kelas $kelas = null): array
    {
        $indikator = Rekap::indikator($periode['dari'], $periode['sampai'], $kelas);
        $lalu = $periode['sebelumnya'] ? Rekap::indikator($periode['sebelumnya']['dari'], $periode['sebelumnya']['sampai'], $kelas) : null;
        $pilar = self::pilar($indikator, $lalu, (bool) $kelas);

        return [
            'indikator' => $indikator,
            'pilar' => $pilar,
            'kalimat' => self::kalimat($pilar, $indikator, ($kelas ? 'Kelas '.$kelas->nama.', ' : '').$periode['label'], $periode['sebelumnya']['label'] ?? null, $kelas?->nama),
            'hari' => Rekap::hariKegiatan($periode['dari'], $periode['sampai'])->count(),
        ];
    }

    /**
     * Murid satu kelas yang perlu perhatian beserta alasannya:
     * belum aktif membaca, atau Budaya Membaca / Kemampuan Literasi di bawah kategori Baik (Cukup atau Perlu Bimbingan).
     */
    public static function muridPerluPerhatian(array $rekapKelas): Collection
    {
        $batas = Pengaturan::angka('batas_baik');

        return $rekapKelas['murid']->map(function ($b) use ($batas) {
            $alasan = [];
            if (! $b['aktif']) {
                $alasan[] = 'hadir membaca '.Rekap::persenTeks($b['hadir_persen']);
            }
            if ($b['budaya_persen'] !== null && $b['budaya_persen'] < $batas) {
                $alasan[] = 'Budaya Membaca '.Rekap::persenTeks($b['budaya_persen']);
            }
            if ($b['literasi_persen'] !== null && $b['literasi_persen'] < $batas) {
                $alasan[] = 'Kemampuan Literasi '.Rekap::persenTeks($b['literasi_persen']);
            }

            return $alasan ? $b + ['alasan' => $alasan] : null;
        })->filter()->sortBy(fn ($b) => $b['budaya_persen'] ?? 0)->values();
    }

    protected static function gabung(array $bagian): string
    {
        $akhir = array_pop($bagian);

        return $bagian ? implode(', ', $bagian).' dan '.$akhir : $akhir;
    }
}
