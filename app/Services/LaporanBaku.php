<?php

namespace App\Services;

use App\Models\KasusPembinaan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Menyusun 4 laporan baku sekolah dalam bentuk netral (judul, kolom, baris)
 * agar bisa dicetak ke PDF maupun Excel dengan isi yang sama.
 *
 * Sel angka berbentuk ['jumlah' => int, 'n' => int, 'persen' => ?float].
 */
class LaporanBaku
{
    public const JUDUL = [
        'budaya' => 'DAFTAR REKAPAN MEMBANGUN BUDAYA BACA',
        'literasi' => 'DAFTAR HADIR KEMAMPUAN LITERASI',
        'pelanggaran' => 'REKAPAN PELANGGARAN',
        'ekosistem' => 'REKAPAN DATA EKOSISTEM LITERAT',
    ];

    /** @param  Collection  $kelasList  koleksi Kelas */
    public static function susun(string $laporan, Collection $kelasList, Carbon $dari, Carbon $sampai): array
    {
        return match ($laporan) {
            'budaya' => self::indikator($kelasList, $dari, $sampai, ['Kehadiran Membaca' => 'hadir', 'Konsisten' => 'konsisten', 'Kelengkapan Jurnal' => 'jurnal']),
            'literasi' => self::indikator($kelasList, $dari, $sampai, ['Memahami' => 'memahami', 'Merangkum' => 'merangkum', 'Menyampaikan Informasi' => 'menyampaikan']),
            'pelanggaran' => self::pelanggaran($kelasList, $dari, $sampai),
            'ekosistem' => self::ekosistem($kelasList, $dari, $sampai),
        } + ['judul' => self::JUDUL[$laporan]];
    }

    protected static function sel(int $jumlah, int $n): array
    {
        return ['jumlah' => $jumlah, 'n' => $n, 'persen' => Rekap::persen($jumlah, $n)];
    }

    protected static function indikator(Collection $kelasList, Carbon $dari, Carbon $sampai, array $kolom): array
    {
        $baris = [];
        $n = 0;
        foreach ($kelasList as $kelas) {
            $rekap = Rekap::rekapKelas($kelas, $dari, $sampai);
            $n = $rekap['n'];
            foreach ($rekap['murid'] as $b) {
                $baris[] = array_merge(
                    [$b['murid']->nama, $kelas->nama],
                    array_map(fn ($k) => self::sel($b[$k], $rekap['n']), array_values($kolom)),
                );
            }
        }

        return ['kolom' => array_merge(['Nama Murid', 'Kelas'], array_keys($kolom)), 'baris' => $baris, 'n' => $n];
    }

    protected static function pelanggaran(Collection $kelasList, Carbon $dari, Carbon $sampai): array
    {
        $kasus = Rekap::kasus($dari, $sampai)->whereIn('kelas_id', $kelasList->pluck('id'))->values();
        $label = fn (?string $t) => $t ? KasusPembinaan::PERKEMBANGAN[$t]['label'] : '-';

        $baris = $kasus->map(function (KasusPembinaan $k) use ($label) {
            $p = $k->pemantauan->keyBy('minggu');

            return array_merge(
                [$k->murid->nama, $k->kelas->nama, $k->kondisi_awal],
                array_map(fn ($m) => $label($p[$m]->tingkat ?? null), range(1, 4)),
                [
                    $k->namaPelanggaran().' ('.KasusPembinaan::TINGKAT[$k->tingkat].')',
                    $k->namaTindakan(),
                    $k->status_akhir ? $label($k->status_akhir) : 'Dalam pembinaan',
                ],
            );
        })->all();

        return [
            'kolom' => ['Nama Murid', 'Kelas', 'Kondisi Awal', 'Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4', 'Jenis Pelanggaran', 'Tindakan Pembinaan', 'Status'],
            'baris' => $baris,
            'n' => null,
        ];
    }

    protected static function ekosistem(Collection $kelasList, Carbon $dari, Carbon $sampai): array
    {
        $baris = [];
        $n = 0;
        foreach ($kelasList as $kelas) {
            $guru = Rekap::rekapGuru($dari, $sampai, $kelas);
            foreach ($guru['guru'] as $g) {
                $baris[] = [$g['guru']->name.' (Guru)', $kelas->nama, self::sel(min($g['hari'], $guru['n']), $guru['n']), $g['karya']];
            }
            $rekap = Rekap::rekapKelas($kelas, $dari, $sampai);
            $n = $rekap['n'];
            foreach ($rekap['murid'] as $b) {
                $baris[] = [$b['murid']->nama, $kelas->nama, self::sel($b['hadir'], $rekap['n']), $b['karya']];
            }
        }

        return ['kolom' => ['Nama Murid/Guru', 'Kelas', 'Memanfaatkan Teras Baca', 'Karya Literasi'], 'baris' => $baris, 'n' => $n];
    }
}
