<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Pengaturan;
use App\Services\LaporanBaku;
use App\Services\Rekap;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;

class RekapUnduhController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'kelas' => ['nullable', 'integer'],
            'bulan' => ['required', 'date_format:Y-m'],
            'laporan' => ['required', 'in:budaya,literasi,pelanggaran,ekosistem'],
            'format' => ['required', 'in:pdf,xlsx'],
            'angka' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        if ($data['kelas'] ?? null) {
            abort_unless($user->bolehAksesKelas($data['kelas']), 403);
            $kelasList = collect([Kelas::findOrFail($data['kelas'])]);
        } else {
            abort_if($user->isGuru(), 403);
            $kelasList = Kelas::aktif()->orderBy('tingkat')->orderBy('nama')->get();
        }

        $tgl = Carbon::createFromFormat('Y-m-d', $data['bulan'].'-01');
        [$dari, $sampai] = Rekap::periodeBulan($tgl->year, $tgl->month);
        $laporan = LaporanBaku::susun($data['laporan'], $kelasList, $dari, $sampai);
        $labelBulan = Rekap::labelBulan($tgl);
        $labelKelas = $kelasList->count() === 1 ? 'Kelas '.$kelasList->first()->nama : 'Semua Kelas';
        $namaFile = Str::slug($laporan['judul'].' '.$labelKelas.' '.$labelBulan);

        if ($data['format'] === 'pdf') {
            return Pdf::loadView('cetak.rekap', [
                'laporan' => $laporan,
                'labelBulan' => $labelBulan,
                'labelKelas' => $labelKelas,
                'kelasList' => $kelasList,
                'angka' => (bool) ($data['angka'] ?? false),
                'batas' => Pengaturan::angka('batas_centang_cetak'),
            ])->setPaper('a4', $data['laporan'] === 'pelanggaran' ? 'landscape' : 'portrait')
                ->stream($namaFile.'.pdf');
        }

        return response()->streamDownload(function () use ($laporan, $labelBulan, $labelKelas) {
            $writer = new Writer;
            $writer->openToFile('php://output');
            $tebal = (new Style)->setFontBold();
            $writer->addRow(Row::fromValues([$laporan['judul']], $tebal));
            $writer->addRow(Row::fromValues([Pengaturan::ambil('nama_sekolah').' · '.$labelKelas.' · '.$labelBulan]));
            if ($laporan['n'] !== null) {
                $writer->addRow(Row::fromValues(['Jumlah hari kegiatan: '.$laporan['n']]));
            }
            $writer->addRow(Row::fromValues([]));

            // Kolom angka dipecah menjadi jumlah centang dan persen
            $kepala = ['No'];
            foreach ($laporan['kolom'] as $i => $k) {
                $angka = isset($laporan['baris'][0][$i]) && is_array($laporan['baris'][0][$i]);
                array_push($kepala, ...($angka ? [$k.' (jumlah)', $k.' (%)'] : [$k]));
            }
            $writer->addRow(Row::fromValues($kepala, $tebal));

            foreach ($laporan['baris'] as $no => $baris) {
                $sel = [$no + 1];
                foreach ($baris as $nilai) {
                    array_push($sel, ...(is_array($nilai) ? [$nilai['jumlah'], $nilai['persen']] : [$nilai]));
                }
                $writer->addRow(Row::fromValues($sel));
            }
            $writer->close();
        }, $namaFile.'.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }
}
