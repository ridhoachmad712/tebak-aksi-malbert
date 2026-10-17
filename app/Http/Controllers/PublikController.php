<?php

namespace App\Http\Controllers;

use App\Models\Diseminasi;
use App\Models\Foto;
use App\Models\KaryaLiterasi;
use App\Models\KegiatanHarian;
use App\Models\KegiatanHarianMurid;
use App\Models\Konten;
use App\Models\ManualBook;
use App\Models\Murid;
use App\Models\Pengaduan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class PublikController extends Controller
{
    public function beranda(): View
    {
        $awalBulan = today()->startOfMonth();

        // Statistik publik hanya berupa angka agregat, tanpa nama murid
        $statistik = [
            'kegiatan' => KegiatanHarian::whereDate('tanggal', '>=', $awalBulan)->count(),
            'membaca' => KegiatanHarianMurid::where('hadir', true)
                ->whereHas('kegiatanHarian', fn ($q) => $q->whereDate('tanggal', '>=', $awalBulan))->count(),
            'karya' => KaryaLiterasi::whereDate('tanggal', '>=', $awalBulan)->count(),
            'murid' => Murid::where('aktif', true)->count(),
        ];

        return view('publik.beranda', [
            'statistik' => $statistik,
            'tentang' => Konten::terbit()->where('jenis', 'halaman')->where('tampil_menu', true)->orderBy('urutan')->first(),
            'berita' => Konten::terbit()->where('jenis', 'berita')->latest('terbit_pada')->limit(3)->get(),
            'galeri' => Foto::where('tampil_galeri', true)->latest()->limit(8)->get(),
        ]);
    }

    public function halaman(string $slug): View
    {
        return view('publik.konten', ['konten' => Konten::terbit()->where('slug', $slug)->firstOrFail()]);
    }

    public function berita(): View
    {
        return view('publik.berita', ['berita' => Konten::terbit()->where('jenis', 'berita')->latest('terbit_pada')->paginate(9)]);
    }

    public function galeri(): View
    {
        return view('publik.galeri', ['foto' => Foto::where('tampil_galeri', true)->latest()->paginate(24)]);
    }

    public function diseminasi(): View
    {
        return view('publik.diseminasi', ['daftar' => Diseminasi::with('foto')->where('terbit', true)->latest('tanggal')->paginate(9)]);
    }

    public function diseminasiDetail(Diseminasi $diseminasi): View
    {
        abort_unless($diseminasi->terbit, 404);

        return view('publik.diseminasi-detail', ['d' => $diseminasi->load('foto')]);
    }

    public function manual(): View
    {
        $untuk = auth()->check() ? ['publik', 'guru'] : ['publik'];

        return view('publik.manual', ['daftar' => ManualBook::whereIn('untuk', $untuk)->orderBy('urutan')->get()]);
    }

    public function formPengaduan(): View
    {
        return view('publik.pengaduan');
    }

    public function kirimPengaduan(Request $request): RedirectResponse
    {
        // Jebakan bot: kolom tersembunyi harus kosong
        if ($request->filled('situs')) {
            return redirect()->route('pengaduan')->with('terkirim', true);
        }

        $kunci = 'pengaduan|'.$request->ip();
        if (RateLimiter::tooManyAttempts($kunci, 3)) {
            return back()->withInput()->with('galat', 'Terlalu banyak kiriman. Silakan coba lagi nanti.');
        }

        $data = $request->validate([
            'nama' => ['nullable', 'string', 'max:100'],
            'kontak' => ['nullable', 'string', 'max:100'],
            'kategori' => ['required', 'in:'.implode(',', array_keys(Pengaduan::KATEGORI))],
            'isi' => ['required', 'string', 'min:10', 'max:3000'],
        ], [], ['isi' => 'isi pengaduan']);

        RateLimiter::hit($kunci, 3600);
        Pengaduan::create($data + ['ip' => $request->ip(), 'status' => 'baru']);

        return redirect()->route('pengaduan')->with('terkirim', true);
    }
}
