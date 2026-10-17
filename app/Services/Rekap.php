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
use Closure;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
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

    /** Simpanan hasil per permintaan (dibuang otomatis setiap permintaan baru). */
    public static function ingat(string $kunci, Closure $hitung): mixed
    {
        $simpanan = app('rekap.simpanan');

        if (! $simpanan->offsetExists($kunci)) {
            $simpanan[$kunci] = $hitung();
        }

        return $simpanan[$kunci];
    }

    public static function tahunAjaranAktif(): ?TahunAjaran
    {
        return self::ingat('ta', fn () => TahunAjaran::aktif());
    }

    /** @return array<string, true> tanggal libur "Y-m-d" */
    protected static function libur(): array
    {
        return self::ingat('libur', fn () => HariLibur::pluck('tanggal')->mapWithKeys(fn ($t) => [$t->toDateString() => true])->all());
    }

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

    public static function labelSemester(int $semester, ?TahunAjaran $ta = null): string
    {
        return 'Semester '.($semester === 1 ? 'Ganjil' : 'Genap').($ta ? ' '.$ta->nama : '');
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

    /** Senin–Jumat dikurangi hari libur, mulai dari awal tahun ajaran aktif, tidak melewati hari ini. */
    public static function hariKegiatan(Carbon $dari, Carbon $sampai): Collection
    {
        $sampai = $sampai->copy()->min(today());
        $ta = self::tahunAjaranAktif();
        if ($ta && $dari->lt($ta->mulai) && $sampai->gte($ta->mulai)) {
            $dari = $ta->mulai->copy();
        }

        $libur = self::libur();
        $hari = collect();
        for ($d = $dari->copy(); $d->lte($sampai); $d->addDay()) {
            if ($d->isWeekday() && ! isset($libur[$d->toDateString()])) {
                $hari->push($d->copy());
            }
        }

        return $hari;
    }

    public static function hariKegiatanIni(?Carbon $tanggal = null): bool
    {
        $tanggal ??= today();

        return $tanggal->isWeekday() && ! isset(self::libur()[$tanggal->toDateString()]);
    }

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

    public static function rerata(array $nilai): ?float
    {
        $nilai = array_filter($nilai, fn ($v) => $v !== null);

        return $nilai ? round(array_sum($nilai) / count($nilai), 1) : null;
    }

    /**
     * Rekap per murid aktif untuk satu kelas dalam satu periode.
     *
     * @return array{n: int, murid: Collection}
     */
    public static function rekapKelas(Kelas $kelas, Carbon $dari, Carbon $sampai): array
    {
        return self::rekapBanyakKelas(collect([$kelas]), $dari, $sampai)[$kelas->id];
    }

    /**
     * Rekap beberapa kelas sekaligus dengan tiga kueri, berapa pun jumlah kelasnya.
     *
     * @return array<int, array{n: int, murid: Collection}>
     */
    public static function rekapBanyakKelas(Collection $kelasList, Carbon $dari, Carbon $sampai): array
    {
        $n = self::hariKegiatan($dari, $sampai)->count();
        $indikator = array_keys(KegiatanHarian::INDIKATOR);
        $rentang = [$dari->toDateString(), $sampai->toDateString()];
        $kelasList = EloquentCollection::make($kelasList->all())->loadMissing('muridAktif');

        $jumlah = DB::table('kegiatan_harian_murid as km')
            ->join('kegiatan_harian as k', 'k.id', '=', 'km.kegiatan_harian_id')
            ->whereIn('k.kelas_id', $kelasList->pluck('id'))
            ->whereBetween('k.tanggal', $rentang)
            ->groupBy('k.kelas_id', 'km.murid_id')
            ->select('k.kelas_id', 'km.murid_id')
            ->addSelect(collect($indikator)->map(fn ($i) => DB::raw("SUM(CASE WHEN km.$i THEN 1 ELSE 0 END) as $i"))->all())
            ->get()
            ->keyBy(fn ($r) => $r->kelas_id.'-'.$r->murid_id);

        $karya = DB::table('karya_murid as kmu')
            ->join('karya_literasi as kl', 'kl.id', '=', 'kmu.karya_literasi_id')
            ->whereBetween('kl.tanggal', $rentang)
            ->groupBy('kmu.murid_id')
            ->selectRaw('kmu.murid_id as id, COUNT(*) as jml')
            ->pluck('jml', 'id');

        $batasAktif = Pengaturan::angka('batas_murid_aktif');
        $hasil = [];
        foreach ($kelasList as $kelas) {
            $hasil[$kelas->id] = [
                'n' => $n,
                'murid' => $kelas->muridAktif->map(function ($m) use ($kelas, $jumlah, $karya, $indikator, $n, $batasAktif) {
                    $baris = ['murid' => $m, 'karya' => (int) ($karya[$m->id] ?? 0)];
                    $data = $jumlah->get($kelas->id.'-'.$m->id);
                    foreach ($indikator as $i) {
                        $baris[$i] = (int) ($data->$i ?? 0);
                        $baris[$i.'_persen'] = self::persen($baris[$i], $n);
                    }
                    $baris['budaya_persen'] = self::rerata([$baris['hadir_persen'], $baris['konsisten_persen'], $baris['jurnal_persen']]);
                    $baris['literasi_persen'] = self::rerata([$baris['memahami_persen'], $baris['merangkum_persen'], $baris['menyampaikan_persen']]);
                    $baris['aktif'] = $baris['hadir_persen'] !== null && $baris['hadir_persen'] >= $batasAktif;

                    return $baris;
                })->values(),
            ];
        }

        return $hasil;
    }

    /**
     * Rekap guru: hari mengisi kegiatan harian (memanfaatkan Teras Baca) dan karya milik guru.
     * Hanya wali kelas yang masuk hitungan "guru aktif", karena guru mata pelajaran
     * tidak bertanggung jawab atas catatan harian setiap hari.
     */
    public static function rekapGuru(Carbon $dari, Carbon $sampai, ?Kelas $kelas = null): array
    {
        $n = self::hariKegiatan($dari, $sampai)->count();
        $rentang = [$dari->toDateString(), $sampai->toDateString()];

        $guru = User::guru()->where('status', 'aktif')->orderBy('name')
            ->with(['kelasWali' => fn ($q) => $q->aktif()])
            ->when($kelas, fn ($q) => $q->where(fn ($w) => $w->whereHas('kelasWali', fn ($k) => $k->whereKey($kelas->id))
                ->orWhereHas('kelasMapel', fn ($k) => $k->whereKey($kelas->id))))
            ->get();

        $hariMengisi = RiwayatPengisian::query()
            ->join('kegiatan_harian as k', 'k.id', '=', 'riwayat_pengisian.kegiatan_harian_id')
            ->whereBetween('k.tanggal', $rentang)
            ->when($kelas, fn ($q) => $q->where('k.kelas_id', $kelas->id))
            ->groupBy('riwayat_pengisian.user_id')
            ->selectRaw('riwayat_pengisian.user_id as id, COUNT(DISTINCT k.tanggal) as jml')
            ->pluck('jml', 'id');

        $karya = KaryaLiterasi::whereNotNull('guru_pemilik_id')
            ->whereBetween('tanggal', $rentang)
            ->groupBy('guru_pemilik_id')
            ->selectRaw('guru_pemilik_id as id, COUNT(*) as jml')
            ->pluck('jml', 'id');

        $batasAktif = Pengaturan::angka('batas_guru_aktif');
        $baris = $guru->map(function ($g) use ($hariMengisi, $karya, $n, $kelas, $batasAktif) {
            $hari = (int) ($hariMengisi[$g->id] ?? 0);
            $persen = self::persen(min($hari, $n), $n);

            return [
                'guru' => $g,
                'wali' => $kelas ? $g->kelasWali->contains('id', $kelas->id) : $g->kelasWali->isNotEmpty(),
                'hari' => $hari,
                'persen' => $persen,
                'karya' => (int) ($karya[$g->id] ?? 0),
                'aktif' => $persen !== null && $persen >= $batasAktif,
            ];
        });

        return ['n' => $n, 'guru' => $baris];
    }

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

    public static function indikator(Carbon $dari, Carbon $sampai, ?Kelas $kelasFilter = null): array
    {
        $kelasList = $kelasFilter ? collect([$kelasFilter]) : Kelas::aktif()->orderBy('tingkat')->orderBy('nama')->get();
        $rekap = self::rekapBanyakKelas($kelasList, $dari, $sampai);

        $semuaMurid = collect();
        $perKelas = [];
        foreach ($kelasList as $kelas) {
            $murid = $rekap[$kelas->id]['murid'];
            $semuaMurid = $semuaMurid->concat($murid);
            $aktif = $murid->where('aktif', true)->count();
            $perKelas[] = [
                'kelas' => $kelas,
                'jumlah' => $murid->count(),
                'aktif' => $aktif,
                'belum' => $murid->count() - $aktif,
                'persen' => self::persen($aktif, $murid->count()),
                'budaya' => self::rerata($murid->pluck('budaya_persen')->all()),
                'literasi' => self::rerata($murid->pluck('literasi_persen')->all()),
            ];
        }

        $wali = self::rekapGuru($dari, $sampai, $kelasFilter)['guru']->where('wali', true);

        // Keberhasilan pembinaan: per murid binaan, dari kasus terakhirnya dalam periode
        $kasus = self::kasus($dari, $sampai, $kelasFilter);
        $statusMurid = $kasus->groupBy('murid_id')->map(fn ($k) => self::statusTerkini($k->sortBy('tanggal')->last()));
        $berhasil = $statusMurid->filter(fn ($s) => in_array($s, ['positif', 'konsisten'], true))->count();

        // Penurunan pelanggaran: semester tempat periode berakhir, dibandingkan baseline sekolah
        $ta = self::tahunAjaranAktif();
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

        $tindakLanjut = $kasus->filter(fn ($k) => self::statusTerkini($k) === 'belum' && ($k->status_akhir || $k->pemantauan->count() >= 4))->values();
        $targetMuridAktif = Pengaturan::angka('target_murid_aktif');

        return [
            'murid_aktif' => [
                'nilai' => self::persen($semuaMurid->where('aktif', true)->count(), $semuaMurid->count()),
                'target' => $targetMuridAktif,
                'keterangan' => $semuaMurid->where('aktif', true)->count().' dari '.$semuaMurid->count().' murid hadir membaca ≥ '.(int) Pengaturan::angka('batas_murid_aktif').'% hari',
            ],
            'guru_aktif' => [
                'nilai' => self::persen($wali->where('aktif', true)->count(), $wali->count()),
                'target' => Pengaturan::angka('target_guru_aktif'),
                'keterangan' => $wali->where('aktif', true)->count().' dari '.$wali->count().' wali kelas mengisi ≥ '.(int) Pengaturan::angka('batas_guru_aktif').'% hari',
            ],
            'keberhasilan_pembinaan' => [
                'nilai' => self::persen($berhasil, $statusMurid->count()),
                'target' => Pengaturan::angka('target_keberhasilan_pembinaan'),
                'keterangan' => $statusMurid->isEmpty() ? 'Belum ada murid binaan pada periode ini' : $berhasil.' dari '.$statusMurid->count().' murid binaan berubah positif',
            ],
            'penurunan_pelanggaran' => [
                'nilai' => $penurunan['persen'] ?? null,
                'target' => Pengaturan::angka('target_penurunan_pelanggaran'),
                'keterangan' => match (true) {
                    ! $penurunan => 'Tahun ajaran aktif belum diatur',
                    (bool) $kelasFilter => 'Hanya dihitung untuk seluruh sekolah',
                    ! $penurunan['baseline'] => "Semester {$penurunan['semester']}: {$penurunan['jumlah']} kasus. Baseline belum diisi admin",
                    default => "Semester {$penurunan['semester']}: {$penurunan['jumlah']} kasus, baseline {$penurunan['baseline']}",
                },
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
            'kelas_di_bawah_target' => collect($perKelas)->filter(fn ($r) => $r['persen'] !== null && $r['persen'] < $targetMuridAktif)->values(),
            'aspek' => self::aspekPerKelas($kasus, $kelasList),
            'tindak_lanjut' => $tindakLanjut,
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
            $hasil[] = [
                'kelas' => $kelas,
                'hitung' => $hitung,
                'total' => $kasusKelas->count(),
                'tertinggi' => $hitung->filter()->sortDesc()->keys()->first(),
            ];
        }

        return ['aspek' => $aspek->pluck('nama')->all(), 'kelas' => $hasil];
    }

    /**
     * Rata-rata kehadiran membaca dan jumlah kasus baru per bulan dalam tahun ajaran aktif.
     * Rata-rata kehadiran = total centang Hadir ÷ (hari kegiatan × jumlah murid aktif).
     */
    public static function trenBulanan(?Kelas $kelasFilter = null): array
    {
        $ta = self::tahunAjaranAktif();
        if (! $ta) {
            return [];
        }

        $kelasIds = $kelasFilter ? [$kelasFilter->id] : Kelas::aktif()->pluck('id')->all();
        $jumlahMurid = DB::table('kelas_murid')->join('murid', 'murid.id', '=', 'kelas_murid.murid_id')
            ->whereIn('kelas_murid.kelas_id', $kelasIds)->where('murid.aktif', true)->count();
        $rentang = [$ta->mulai->toDateString(), today()->min($ta->selesai)->toDateString()];

        $hadirPerTanggal = DB::table('kegiatan_harian_murid as km')
            ->join('kegiatan_harian as k', 'k.id', '=', 'km.kegiatan_harian_id')
            ->join('murid', 'murid.id', '=', 'km.murid_id')
            ->whereIn('k.kelas_id', $kelasIds)->where('murid.aktif', true)
            ->whereBetween('k.tanggal', $rentang)
            ->groupBy('k.tanggal')
            ->selectRaw('k.tanggal as tgl, SUM(CASE WHEN km.hadir THEN 1 ELSE 0 END) as jml')
            ->pluck('jml', 'tgl');

        $kasusPerTanggal = KasusPembinaan::whereIn('kelas_id', $kelasIds)->whereBetween('tanggal', $rentang)->pluck('tanggal');

        $tren = [];
        for ($bulan = $ta->mulai->copy()->startOfMonth(); $bulan->lte(today()) && $bulan->lte($ta->selesai); $bulan->addMonth()) {
            [$dari, $sampai] = self::periodeBulan($bulan->year, $bulan->month);
            $awalan = $bulan->format('Y-m');
            $hadir = $hadirPerTanggal->filter(fn ($j, $t) => str_starts_with((string) $t, $awalan))->sum();
            $tren[] = [
                'label' => mb_substr(self::BULAN[$bulan->month], 0, 3),
                'hadir' => self::persen($hadir, self::hariKegiatan($dari, $sampai)->count() * $jumlahMurid),
                'kasus' => $kasusPerTanggal->filter(fn ($t) => $t->format('Y-m') === $awalan)->count(),
            ];
        }

        return $tren;
    }
}
