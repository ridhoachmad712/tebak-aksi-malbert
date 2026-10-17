<?php

namespace App\Services;

use App\Models\AspekKarakter;
use App\Models\BaselinePelanggaran;
use App\Models\HariLibur;
use App\Models\KaryaLiterasi;
use App\Models\KasusPembinaan;
use App\Models\KegiatanHarian;
use App\Models\Kelas;
use App\Models\Pengaturan;
use App\Models\RiwayatPengisian;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Seluruh perhitungan rekap dan indikator Monev.
 * Rekap selalu dihitung dari isian harian guru; tidak ada isian yang dibuat otomatis.
 */
class Rekap
{
    public const BULAN = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    // ---------------------------------------------------------------- Periode

    /** @return array{0: Carbon, 1: Carbon} */
    public static function periodeBulan(int $tahun, int $bulan): array
    {
        $dari = Carbon::create($tahun, $bulan, 1)->startOfDay();

        return [$dari, $dari->copy()->endOfMonth()->startOfDay()];
    }

    /** Semester 1 (Ganjil): Juli–Desember. Semester 2 (Genap): Januari–Juni. */
    public static function periodeSemester(TahunAjaran $ta, int $semester): array
    {
        $tahunMulai = $ta->mulai->year;

        return $semester === 1
            ? [Carbon::create($tahunMulai, 7, 1), Carbon::create($tahunMulai, 12, 31)]
            : [Carbon::create($tahunMulai + 1, 1, 1), Carbon::create($tahunMulai + 1, 6, 30)];
    }

    public static function semesterDari(Carbon $tanggal): int
    {
        return $tanggal->month >= 7 ? 1 : 2;
    }

    public static function labelBulan(Carbon $tanggal): string
    {
        return self::BULAN[$tanggal->month].' '.$tanggal->year;
    }

    public static function namaHari(Carbon $tanggal): string
    {
        return ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][$tanggal->dayOfWeek];
    }

    public static function tanggalPanjang(Carbon $tanggal): string
    {
        return self::namaHari($tanggal).', '.$tanggal->day.' '.self::BULAN[$tanggal->month].' '.$tanggal->year;
    }

    // ---------------------------------------------------------------- Hari kegiatan

    /** Senin–Jumat dikurangi hari libur, tidak melewati hari ini. */
    public static function hariKegiatan(Carbon $dari, Carbon $sampai): Collection
    {
        $sampai = $sampai->copy()->min(today());

        // Tidak menghitung hari sebelum tahun ajaran aktif dimulai
        $ta = TahunAjaran::aktif();
        if ($ta && $dari->lt($ta->mulai) && $sampai->gte($ta->mulai)) {
            $dari = $ta->mulai->copy();
        }

        if ($sampai->lt($dari)) {
            return collect();
        }

        $libur = HariLibur::whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])
            ->pluck('tanggal')->map(fn ($t) => $t->toDateString())->flip();

        $hari = collect();
        for ($d = $dari->copy(); $d->lte($sampai); $d->addDay()) {
            if ($d->isWeekday() && ! $libur->has($d->toDateString())) {
                $hari->push($d->copy());
            }
        }

        return $hari;
    }

    public static function hariKegiatanIni(?Carbon $tanggal = null): bool
    {
        $tanggal ??= today();

        return $tanggal->isWeekday() && ! HariLibur::whereDate('tanggal', $tanggal)->exists();
    }

    // ---------------------------------------------------------------- Kategori

    /** @return array{label: string, warna: string} */
    public static function kategori(?float $persen): array
    {
        if ($persen === null) {
            return ['label' => '-', 'warna' => 'gray'];
        }

        return match (true) {
            $persen >= Pengaturan::angka('batas_sangat_baik') => ['label' => 'Sangat Baik', 'warna' => 'emerald'],
            $persen >= Pengaturan::angka('batas_baik') => ['label' => 'Baik', 'warna' => 'sky'],
            $persen >= Pengaturan::angka('batas_cukup') => ['label' => 'Cukup', 'warna' => 'amber'],
            default => ['label' => 'Perlu Bimbingan', 'warna' => 'rose'],
        };
    }

    public static function persen(int|float $bagian, int|float $total): ?float
    {
        return $total > 0 ? round($bagian / $total * 100, 1) : null;
    }

    // ---------------------------------------------------------------- Rekap murid

    /**
     * Rekap per murid untuk satu kelas dalam satu periode.
     *
     * @return array{n: int, murid: Collection}
     */
    public static function rekapKelas(Kelas $kelas, Carbon $dari, Carbon $sampai): array
    {
        $n = self::hariKegiatan($dari, $sampai)->count();
        $indikator = array_keys(KegiatanHarian::INDIKATOR);

        $jumlah = DB::table('kegiatan_harian_murid as km')
            ->join('kegiatan_harian as k', 'k.id', '=', 'km.kegiatan_harian_id')
            ->where('k.kelas_id', $kelas->id)
            ->whereBetween('k.tanggal', [$dari->toDateString(), $sampai->toDateString()])
            ->groupBy('km.murid_id')
            ->select('km.murid_id')
            ->addSelect(collect($indikator)->map(fn ($i) => DB::raw("SUM(CASE WHEN km.$i THEN 1 ELSE 0 END) as $i"))->all())
            ->get()->keyBy('murid_id');

        $karya = DB::table('karya_murid as kmu')
            ->join('karya_literasi as kl', 'kl.id', '=', 'kmu.karya_literasi_id')
            ->whereBetween('kl.tanggal', [$dari->toDateString(), $sampai->toDateString()])
            ->groupBy('kmu.murid_id')
            ->selectRaw('kmu.murid_id as id, COUNT(*) as jml')
            ->pluck('jml', 'id');

        $murid = $kelas->murid()->get()->map(function ($m) use ($jumlah, $karya, $indikator, $n) {
            $baris = ['murid' => $m, 'karya' => (int) ($karya[$m->id] ?? 0)];
            foreach ($indikator as $i) {
                $baris[$i] = (int) ($jumlah[$m->id]->$i ?? 0);
                $baris[$i.'_persen'] = self::persen($baris[$i], $n);
            }
            $baris['budaya_persen'] = self::rerata([$baris['hadir_persen'], $baris['konsisten_persen'], $baris['jurnal_persen']]);
            $baris['literasi_persen'] = self::rerata([$baris['memahami_persen'], $baris['merangkum_persen'], $baris['menyampaikan_persen']]);
            $baris['aktif'] = $baris['hadir_persen'] !== null && $baris['hadir_persen'] >= Pengaturan::angka('batas_murid_aktif');

            return $baris;
        });

        return ['n' => $n, 'murid' => $murid];
    }

    /** Rekap guru: hari mengisi kegiatan harian (memanfaatkan Teras Baca) dan karya milik guru. */
    public static function rekapGuru(Carbon $dari, Carbon $sampai, ?Kelas $kelas = null): array
    {
        $n = self::hariKegiatan($dari, $sampai)->count();

        $guru = User::guru()->where('status', 'aktif')->orderBy('name')
            ->when($kelas, fn ($q) => $q->where(fn ($w) => $w->whereHas('kelasWali', fn ($k) => $k->whereKey($kelas->id))
                ->orWhereHas('kelasMapel', fn ($k) => $k->whereKey($kelas->id))))
            ->get();

        $hariMengisi = RiwayatPengisian::query()
            ->join('kegiatan_harian as k', 'k.id', '=', 'riwayat_pengisian.kegiatan_harian_id')
            ->whereBetween('k.tanggal', [$dari->toDateString(), $sampai->toDateString()])
            ->when($kelas, fn ($q) => $q->where('k.kelas_id', $kelas->id))
            ->groupBy('riwayat_pengisian.user_id')
            ->selectRaw('riwayat_pengisian.user_id as id, COUNT(DISTINCT k.tanggal) as jml')
            ->pluck('jml', 'id');

        $karya = KaryaLiterasi::whereNotNull('guru_pemilik_id')
            ->whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])
            ->groupBy('guru_pemilik_id')
            ->selectRaw('guru_pemilik_id as id, COUNT(*) as jml')
            ->pluck('jml', 'id');

        $baris = $guru->map(function ($g) use ($hariMengisi, $karya, $n) {
            $hari = (int) ($hariMengisi[$g->id] ?? 0);
            $persen = self::persen(min($hari, $n), $n);

            return [
                'guru' => $g,
                'hari' => $hari,
                'persen' => $persen,
                'karya' => (int) ($karya[$g->id] ?? 0),
                'aktif' => $persen !== null && $persen >= Pengaturan::angka('batas_guru_aktif'),
            ];
        });

        return ['n' => $n, 'guru' => $baris];
    }

    /** Kasus pembinaan yang dicatat dalam periode. */
    public static function kasus(Carbon $dari, Carbon $sampai, ?Kelas $kelas = null): Collection
    {
        return KasusPembinaan::with(['murid', 'kelas', 'jenisPelanggaran.aspekKarakter', 'tindakanPembinaan', 'pemantauan'])
            ->whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])
            ->when($kelas, fn ($q) => $q->where('kelas_id', $kelas->id))
            ->orderBy('tanggal')
            ->get();
    }

    /** Status terkini kasus: status akhir, atau hasil pemantauan terakhir. */
    public static function statusTerkini(KasusPembinaan $kasus): ?string
    {
        return $kasus->status_akhir ?? $kasus->pemantauan->sortBy('minggu')->last()?->tingkat;
    }

    // ---------------------------------------------------------------- Indikator Monev

    public static function indikator(Carbon $dari, Carbon $sampai, ?Kelas $kelasFilter = null): array
    {
        $kelasList = $kelasFilter ? collect([$kelasFilter]) : Kelas::aktif()->orderBy('tingkat')->orderBy('nama')->get();

        $semuaMurid = collect();
        $perKelas = [];
        foreach ($kelasList as $kelas) {
            $rekap = self::rekapKelas($kelas, $dari, $sampai);
            $semuaMurid = $semuaMurid->concat($rekap['murid']);
            $aktif = $rekap['murid']->where('aktif', true)->count();
            $perKelas[] = [
                'kelas' => $kelas,
                'jumlah' => $rekap['murid']->count(),
                'aktif' => $aktif,
                'belum' => $rekap['murid']->count() - $aktif,
                'persen' => self::persen($aktif, $rekap['murid']->count()),
                'budaya' => self::rerata($rekap['murid']->pluck('budaya_persen')->all()),
                'literasi' => self::rerata($rekap['murid']->pluck('literasi_persen')->all()),
            ];
        }

        $guru = self::rekapGuru($dari, $sampai, $kelasFilter)['guru'];

        // Keberhasilan pembinaan: per murid binaan, dari kasus terakhirnya dalam periode
        $kasus = self::kasus($dari, $sampai, $kelasFilter);
        $statusMurid = $kasus->groupBy('murid_id')->map(fn ($k) => self::statusTerkini($k->sortBy('tanggal')->last()));
        $berhasil = $statusMurid->filter(fn ($s) => in_array($s, ['positif', 'konsisten'], true))->count();

        // Penurunan pelanggaran: semester tempat periode berakhir
        $ta = TahunAjaran::aktif();
        $penurunan = null;
        if ($ta) {
            $semester = self::semesterDari($sampai);
            [$sDari, $sSampai] = self::periodeSemester($ta, $semester);
            $baseline = BaselinePelanggaran::where('tahun_ajaran_id', $ta->id)->where('semester', $semester)->value('jumlah');
            $jumlahSemester = KasusPembinaan::whereBetween('tanggal', [$sDari->toDateString(), $sSampai->toDateString()])
                ->when($kelasFilter, fn ($q) => $q->where('kelas_id', $kelasFilter->id))->count();
            $penurunan = [
                'semester' => $semester === 1 ? 'Ganjil' : 'Genap',
                'baseline' => $baseline,
                'jumlah' => $jumlahSemester,
                'persen' => $baseline && ! $kelasFilter ? round(($baseline - $jumlahSemester) / $baseline * 100, 1) : null,
            ];
        }

        return [
            'murid_aktif' => [
                'nilai' => self::persen($semuaMurid->where('aktif', true)->count(), $semuaMurid->count()),
                'target' => Pengaturan::angka('target_murid_aktif'),
                'keterangan' => $semuaMurid->where('aktif', true)->count().' dari '.$semuaMurid->count().' murid',
            ],
            'guru_aktif' => [
                'nilai' => self::persen($guru->where('aktif', true)->count(), $guru->count()),
                'target' => Pengaturan::angka('target_guru_aktif'),
                'keterangan' => $guru->where('aktif', true)->count().' dari '.$guru->count().' guru',
            ],
            'keberhasilan_pembinaan' => [
                'nilai' => self::persen($berhasil, $statusMurid->count()),
                'target' => Pengaturan::angka('target_keberhasilan_pembinaan'),
                'keterangan' => $berhasil.' dari '.$statusMurid->count().' murid binaan',
            ],
            'penurunan_pelanggaran' => [
                'nilai' => $penurunan['persen'] ?? null,
                'target' => Pengaturan::angka('target_penurunan_pelanggaran'),
                'keterangan' => $penurunan
                    ? ($penurunan['baseline']
                        ? "Semester {$penurunan['semester']}: {$penurunan['jumlah']} kasus (baseline {$penurunan['baseline']})"
                        : "Semester {$penurunan['semester']}: {$penurunan['jumlah']} kasus · baseline belum diisi admin")
                    : 'Tahun ajaran aktif belum diatur',
            ],
            'budaya_membaca' => [
                'nilai' => self::rerata($semuaMurid->pluck('budaya_persen')->all()),
                'target' => Pengaturan::angka('target_budaya_membaca'),
                'keterangan' => 'Rata-rata hadir, konsisten, dan jurnal',
            ],
            'kemampuan_literasi' => [
                'nilai' => self::rerata($semuaMurid->pluck('literasi_persen')->all()),
                'target' => Pengaturan::angka('target_kemampuan_literasi'),
                'keterangan' => 'Rata-rata memahami, merangkum, dan menyampaikan',
            ],
            'per_kelas' => $perKelas,
            'aspek' => self::aspekPerKelas($kasus, $kelasList),
            'tindak_lanjut' => $kasus->filter(fn ($k) => self::statusTerkini($k) === 'belum' && ($k->status_akhir || $k->pemantauan->count() >= 4))->values(),
        ];
    }

    /** Jumlah kasus per aspek karakter untuk setiap kelas. */
    public static function aspekPerKelas(Collection $kasus, Collection $kelasList): array
    {
        $aspek = AspekKarakter::urut()->get();
        $hasil = [];
        foreach ($kelasList as $kelas) {
            $kasusKelas = $kasus->where('kelas_id', $kelas->id);
            $hitung = $aspek->mapWithKeys(fn ($a) => [$a->nama => $kasusKelas->filter(fn ($k) => $k->jenisPelanggaran?->aspek_karakter_id === $a->id)->count()]);
            $lain = $kasusKelas->filter(fn ($k) => ! $k->jenisPelanggaran?->aspek_karakter_id)->count();
            if ($lain) {
                $hitung['Lainnya'] = $lain;
            }
            $tertinggi = $hitung->filter()->sortDesc();
            $hasil[] = [
                'kelas' => $kelas,
                'hitung' => $hitung,
                'total' => $kasusKelas->count(),
                'tertinggi' => $tertinggi->keys()->first(),
            ];
        }

        return ['aspek' => $aspek->pluck('nama')->all(), 'kelas' => $hasil];
    }

    /** Tren bulanan dalam tahun ajaran: rata-rata kehadiran dan jumlah kasus. */
    public static function trenBulanan(?Kelas $kelasFilter = null): array
    {
        $ta = TahunAjaran::aktif();
        if (! $ta) {
            return [];
        }

        $kelasList = $kelasFilter ? collect([$kelasFilter]) : Kelas::aktif()->get();
        $tren = [];
        for ($bulan = $ta->mulai->copy()->startOfMonth(); $bulan->lte(today()) && $bulan->lte($ta->selesai); $bulan->addMonth()) {
            [$dari, $sampai] = self::periodeBulan($bulan->year, $bulan->month);
            $hadir = $kelasList->flatMap(fn ($k) => self::rekapKelas($k, $dari, $sampai)['murid']->pluck('hadir_persen'));
            $tren[] = [
                'label' => mb_substr(self::BULAN[$bulan->month], 0, 3),
                'hadir' => self::rerata($hadir->all()),
                'kasus' => KasusPembinaan::whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])
                    ->when($kelasFilter, fn ($q) => $q->where('kelas_id', $kelasFilter->id))->count(),
            ];
        }

        return $tren;
    }

    public static function rerata(array $nilai): ?float
    {
        $nilai = array_filter($nilai, fn ($v) => $v !== null);

        return $nilai ? round(array_sum($nilai) / count($nilai), 1) : null;
    }
}
