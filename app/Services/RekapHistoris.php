<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Capaian bulanan dari rekap sekolah, tanpa membuat catatan individual pengganti. */
class RekapHistoris
{
    public static function bulan(string $bulan): ?array
    {
        $ta = Rekap::tahunAjaranAktif();
        if (! $ta || ! Schema::hasTable('rekap_historis_bulanan')) {
            return null;
        }

        $baris = DB::table('rekap_historis_bulanan')
            ->where('tahun_ajaran_id', $ta->id)
            ->where('bulan', $bulan.'-01')->first();
        if (! $baris) {
            return null;
        }

        $data = json_decode($baris->data, true, 512, JSON_THROW_ON_ERROR);

        return ['data' => $data, 'sumber' => $baris->sumber, 'catatan' => $baris->catatan];
    }

    public static function aspek(array $rekap): array
    {
        $d = $rekap['data'];
        $n = (int) $d['jumlah_murid'];
        $minggu = $d['pelanggar_mingguan'];
        $sebelumnya = $d['pelanggar_bulan_sebelumnya'] ?? null;
        $penurunan = $sebelumnya > 0
            ? round(($sebelumnya - $d['murid_binaan']) / $sebelumnya * 100, 1) : null;
        $berkarya = $d['murid_berkarya'] ?? null;
        $aspek = [
            [
                'nama' => 'Budaya Membaca',
                'uraian' => 'capaian budaya membaca dari rekap bulanan sekolah',
                'capaian' => $d['budaya']." dari {$n} murid",
                'nilai' => Rekap::persen($d['budaya'], $n),
                'target' => (float) $d['target_budaya'],
                'jumlah' => (int) $d['budaya'], 'dari' => $n, 'satuan' => 'murid',
            ],
            [
                'nama' => 'Kemampuan Literasi',
                'uraian' => 'capaian kemampuan literasi dari rekap bulanan sekolah',
                'capaian' => $d['literasi']." dari {$n} murid",
                'nilai' => Rekap::persen($d['literasi'], $n),
                'target' => (float) $d['target_literasi'],
                'jumlah' => (int) $d['literasi'], 'dari' => $n, 'satuan' => 'murid',
            ],
            [
                'nama' => 'Penguatan Karakter',
                'uraian' => 'penurunan jumlah murid yang melanggar dibanding bulan sebelumnya',
                'capaian' => $d['murid_binaan'].' murid berbeda mendapat pembinaan',
                'nilai' => $penurunan,
                'target' => (float) $d['target_penurunan'],
                'rincian' => collect($minggu)->map(fn ($j, $i) => 'Minggu '.($i + 1).': '.$j)->join(' / '),
                'mingguan' => array_values(array_map('intval', $minggu)),
                'pembanding' => $sebelumnya > 0 ? 'Bulan ini '.$d['murid_binaan'].' murid, bulan sebelumnya '.$sebelumnya.' murid' : null,
                'kosong' => 'Penurunan bulanan belum dapat dinilai karena data pembanding belum tersedia.',
            ],
            [
                'nama' => 'Kepatuhan Perilaku',
                'uraian' => 'murid binaan yang mengalami perubahan positif dalam rekap sekolah',
                'capaian' => $d['murid_berubah'].' dari '.$d['murid_binaan'].' murid binaan',
                'nilai' => Rekap::persen($d['murid_berubah'], $d['murid_binaan']),
                'target' => (float) $d['target_pembinaan'],
                'jumlah' => (int) $d['murid_berubah'], 'dari' => (int) $d['murid_binaan'], 'satuan' => 'murid binaan',
            ],
            [
                'nama' => 'Ekosistem Literat',
                'uraian' => 'partisipasi murid dan pemerataan kepemilikan karya',
                'capaian' => $d['murid_aktif']." dari {$n} murid aktif; ".$d['jumlah_karya'].' karya dilaporkan',
                'nilai' => Rekap::persen($d['murid_aktif'], $n),
                'target' => (float) $d['target_aktif'],
                'nilai2' => $berkarya === null ? null : Rekap::persen($berkarya, $n),
                'target2' => (float) $d['target_berkarya'],
                'jumlah' => (int) $d['murid_aktif'], 'dari' => $n, 'satuan' => 'murid aktif',
                'jumlah2' => $berkarya === null ? null : (int) $berkarya, 'dari2' => $n, 'jumlah_karya' => (int) $d['jumlah_karya'],
                'belum_cukup' => $berkarya === null,
                'keterangan_status' => 'Partisipasi tercapai. Kepemilikan karya per murid masih perlu dilengkapi; bukti dapat diunggah kemudian.',
            ],
        ];

        foreach ($aspek as &$a) {
            $a['status'] = match (true) {
                $a['nilai'] === null => 'tanpa_data',
                $a['belum_cukup'] ?? false => 'belum_cukup',
                $a['nilai'] < $a['target'] || (isset($a['nilai2']) && $a['nilai2'] < $a['target2']) => 'kurang',
                default => 'tercapai',
            };
        }
        unset($a);

        $dinilai = collect($aspek)->whereIn('status', ['tercapai', 'kurang']);

        return [
            'aspek' => $aspek,
            'jumlah_murid' => $n,
            'tercapai' => $dinilai->where('status', 'tercapai')->count(),
            'dinilai' => $dinilai->count(),
            'sumber_historis' => $rekap['sumber'],
            'catatan_historis' => $rekap['catatan'],
        ];
    }
}
