<?php

namespace App\Services;

use App\Models\KasusPembinaan;
use App\Models\Kelas;
use App\Models\Pengaturan;
use Illuminate\Support\Carbon;

/**
 * Capaian lima aspek inovasi TEBAK AKSI untuk satu bulan, dihitung per jumlah murid
 * seperti pada laporan hasil inovasi sekolah:
 * Budaya Membaca, Kemampuan Literasi, Penguatan Karakter, Kepatuhan Perilaku, Ekosistem Literat.
 */
class AspekInovasi
{
    /**
     * @return array{aspek: list<array>, jumlah_murid: int, tercapai: int, dinilai: int}
     */
    public static function bulan(string $bulan): array
    {
        $tgl = Carbon::createFromFormat('!Y-m', $bulan);
        [$dari, $sampai] = Rekap::periodeBulan($tgl->year, $tgl->month);
        $lalu = $tgl->copy()->subMonth();
        [$dariLalu, $sampaiLalu] = Rekap::periodeBulan($lalu->year, $lalu->month);

        $kelasList = Kelas::aktif()->orderBy('tingkat')->orderBy('nama')->get();
        $murid = collect(Rekap::rekapBanyakKelas($kelasList, $dari, $sampai))->flatMap(fn ($r) => $r['murid']);
        $n = $murid->count();
        $batas = Pengaturan::angka('batas_murid_aktif');
        $adaHari = Rekap::hariKegiatan($dari, $sampai)->isNotEmpty();

        // 1. Budaya Membaca: murid yang hadir, konsisten, dan mengisi jurnal (rata-ratanya) minimal batas hari
        $budaya = $murid->filter(fn ($b) => $b['budaya_persen'] !== null && $b['budaya_persen'] >= $batas)->count();
        // 2. Kemampuan Literasi: murid yang memahami, merangkum, dan menyampaikan (rata-ratanya) minimal batas hari
        $literasi = $murid->filter(fn ($b) => $b['literasi_persen'] !== null && $b['literasi_persen'] >= $batas)->count();

        // 3. Penguatan Karakter: jumlah murid berbeda yang melanggar, bulan ini dibanding bulan lalu, dan per minggu
        $kelasIds = $kelasList->pluck('id');
        $kasusBulan = KasusPembinaan::whereIn('kelas_id', $kelasIds)
            ->whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])->get(['murid_id', 'tanggal', 'status_akhir']);
        $pelanggarIni = $kasusBulan->pluck('murid_id')->unique()->count();
        $pelanggarLalu = KasusPembinaan::whereIn('kelas_id', $kelasIds)
            ->whereBetween('tanggal', [$dariLalu->toDateString(), $sampaiLalu->toDateString()])->distinct()->count('murid_id');
        $perMinggu = [];
        foreach ([1, 2, 3, 4] as $m) {
            // Minggu ke-1: tanggal 1–7, ke-2: 8–14, ke-3: 15–21, ke-4: 22 sampai akhir bulan
            $perMinggu[$m] = $kasusBulan->filter(fn ($k) => min(4, (int) ceil($k->tanggal->day / 7)) === $m)->pluck('murid_id')->unique()->count();
        }
        $penurunan = $pelanggarLalu > 0 ? round(($pelanggarLalu - $pelanggarIni) / $pelanggarLalu * 100, 1) : null;

        // 4. Kepatuhan Perilaku: murid binaan bulan ini yang status terakhirnya berubah positif atau konsisten positif
        $kasusDetail = Rekap::kasus($dari, $sampai);
        $statusMurid = $kasusDetail->groupBy('murid_id')->map(fn ($k) => Rekap::statusTerkini($k->sortBy('tanggal')->last()));
        $binaan = $statusMurid->count();
        $berubah = $statusMurid->filter(fn ($s) => in_array($s, ['positif', 'konsisten'], true))->count();

        // 5. Ekosistem Literat: murid aktif memanfaatkan Teras Baca dan murid yang menghasilkan minimal 1 karya
        $aktif = $murid->where('aktif', true)->count();
        $berkarya = $murid->filter(fn ($b) => $b['karya'] >= 1)->count();

        $persen = fn ($a, $b) => Rekap::persen($a, $b);
        $aspek = [
            [
                'nama' => 'Budaya Membaca',
                'uraian' => "murid membaca 15 menit secara konsisten dan mengisi jurnal (≥ ".(int) $batas.'% hari)',
                'capaian' => "{$budaya} dari {$n} murid",
                'nilai' => $adaHari ? $persen($budaya, $n) : null,
                'target' => Pengaturan::angka('target_aspek_budaya'),
            ],
            [
                'nama' => 'Kemampuan Literasi',
                'uraian' => 'murid memahami, merangkum, atau menceritakan kembali isi bacaan (≥ '.(int) $batas.'% hari)',
                'capaian' => "{$literasi} dari {$n} murid",
                'nilai' => $adaHari ? $persen($literasi, $n) : null,
                'target' => Pengaturan::angka('target_aspek_literasi'),
            ],
            [
                'nama' => 'Penguatan Karakter',
                'uraian' => 'penurunan jumlah murid yang melanggar dibanding '.Rekap::labelBulan($lalu),
                'capaian' => "{$pelanggarIni} murid melanggar (".Rekap::labelBulan($lalu).": {$pelanggarLalu})",
                'nilai' => $penurunan,
                'target' => Pengaturan::angka('target_penurunan_bulanan'),
                'rincian' => collect($perMinggu)->map(fn ($j, $m) => "Minggu {$m}: {$j}")->join(' · '),
                'kosong' => $pelanggarLalu === 0 ? 'Belum bisa dihitung: bulan lalu tidak ada pelanggaran yang dicatat.' : null,
            ],
            [
                'nama' => 'Kepatuhan Perilaku',
                'uraian' => 'murid yang dibina dan mengalami perubahan perilaku (berubah positif atau konsisten positif)',
                'capaian' => "{$berubah} dari {$binaan} murid binaan",
                'nilai' => $persen($berubah, $binaan),
                'target' => Pengaturan::angka('target_keberhasilan_pembinaan'),
                'kosong' => $binaan === 0 ? 'Tidak ada murid yang dibina bulan ini.' : null,
            ],
            [
                'nama' => 'Ekosistem Literat',
                'uraian' => 'murid aktif memanfaatkan Teras Baca dan murid yang menghasilkan minimal 1 karya',
                'capaian' => "{$aktif} dari {$n} murid aktif · {$berkarya} dari {$n} murid berkarya",
                // Tercapai bila keduanya tercapai; yang ditampilkan nilai yang paling jauh dari targetnya
                'nilai' => $adaHari ? $persen($aktif, $n) : null,
                'target' => Pengaturan::angka('target_murid_aktif'),
                'nilai2' => $adaHari ? $persen($berkarya, $n) : null,
                'target2' => Pengaturan::angka('target_murid_berkarya'),
            ],
        ];

        foreach ($aspek as &$a) {
            $kurang1 = $a['nilai'] !== null && $a['nilai'] < $a['target'];
            $kurang2 = isset($a['nilai2']) && $a['nilai2'] !== null && $a['nilai2'] < $a['target2'];
            $a['status'] = match (true) {
                $a['nilai'] === null => 'tanpa_data',
                $kurang1 || $kurang2 => 'kurang',
                default => 'tercapai',
            };
        }
        unset($a);

        $dinilai = collect($aspek)->where('status', '!=', 'tanpa_data');

        return [
            'aspek' => $aspek,
            'jumlah_murid' => $n,
            'tercapai' => $dinilai->where('status', 'tercapai')->count(),
            'dinilai' => $dinilai->count(),
        ];
    }
}
