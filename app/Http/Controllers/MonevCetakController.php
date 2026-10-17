<?php

namespace App\Http\Controllers;

use App\Services\Rekap;
use App\Services\RingkasanMonev;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** Ringkasan Monev satu halaman untuk rapat atau arsip kepala sekolah. */
class MonevCetakController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'periode' => ['nullable', 'in:bulan,semester'],
            'bulan' => ['nullable', 'date_format:Y-m'],
            'semester' => ['nullable', 'in:1,2'],
        ]);

        $jenis = ($data['periode'] ?? 'bulan') === 'semester' && Rekap::tahunAjaranAktif() ? 'semester' : 'bulan';
        $periode = Rekap::periode($jenis, $data['bulan'] ?? today()->format('Y-m'), $data['semester'] ?? (string) Rekap::semesterDari(today()));

        return Pdf::loadView('cetak.monev', RingkasanMonev::susun($periode) + [
            'label' => $periode['label'],
            'labelLalu' => $periode['sebelumnya']['label'] ?? null,
        ])->setPaper('a4', 'portrait')->stream(Str::slug('Ringkasan Monev TEBAK AKSI '.$periode['label']).'.pdf');
    }
}
