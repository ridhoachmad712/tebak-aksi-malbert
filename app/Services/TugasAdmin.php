<?php

namespace App\Services;

use App\Filament\Pages\CadanganData;
use App\Filament\Pages\PengaturanTarget;
use App\Filament\Pages\TampilanSitus;
use App\Filament\Resources\KasusPembinaanResource;
use App\Filament\Resources\KelasResource;
use App\Filament\Resources\KontenResource;
use App\Filament\Resources\ManualBookResource;
use App\Filament\Resources\MuridResource;
use App\Filament\Resources\PengaduanResource;
use App\Filament\Resources\PenggunaResource;
use App\Filament\Resources\TahunAjaranResource;
use App\Models\BaselinePelanggaran;
use App\Models\KasusPembinaan;
use App\Models\KegiatanHarian;
use App\Models\Kelas;
use App\Models\Konten;
use App\Models\ManualBook;
use App\Models\Murid;
use App\Models\Pengaduan;
use App\Models\Pengaturan;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/** Data untuk dasbor admin: persiapan sistem, tugas yang perlu ditindaklanjuti, dan pencatatan kelas. */
class TugasAdmin
{
    /** @return list<array{label: string, selesai: bool, url: string}> */
    public static function persiapan(): array
    {
        $ta = Rekap::tahunAjaranAktif();
        $kelas = Kelas::aktif();

        return [
            ['label' => 'Tahun ajaran aktif sudah diatur', 'selesai' => (bool) $ta, 'url' => TahunAjaranResource::getUrl('index')],
            ['label' => 'Kelas sudah dibuat', 'selesai' => (clone $kelas)->exists(), 'url' => KelasResource::getUrl('index')],
            ['label' => 'Setiap kelas sudah punya wali kelas', 'selesai' => (clone $kelas)->exists() && ! (clone $kelas)->whereNull('wali_kelas_id')->exists(), 'url' => KelasResource::getUrl('index')],
            ['label' => 'Akun guru sudah dibuat dan disetujui', 'selesai' => User::guru()->where('status', 'aktif')->exists(), 'url' => PenggunaResource::getUrl('index')],
            ['label' => 'Murid sudah dimasukkan ke kelas', 'selesai' => Murid::where('aktif', true)->whereHas('kelas', fn ($q) => $q->aktif())->exists(), 'url' => MuridResource::getUrl('index')],
            ['label' => 'Identitas sekolah dan logo sudah diisi', 'selesai' => filled(Pengaturan::ambil('logo_sekolah')) && filled(Pengaturan::ambil('alamat_sekolah')), 'url' => TampilanSitus::getUrl()],
            ['label' => 'Nama kepala sekolah untuk laporan cetak sudah diisi', 'selesai' => filled(Pengaturan::ambil('nama_kepala_sekolah')), 'url' => PengaturanTarget::getUrl()],
            ['label' => 'Baseline pelanggaran semester ini sudah diisi', 'selesai' => $ta && BaselinePelanggaran::where('tahun_ajaran_id', $ta->id)->where('semester', Rekap::semesterDari(today()))->exists(), 'url' => PengaturanTarget::getUrl()],
            ['label' => 'Halaman "Tentang TEBAK AKSI" sudah dibuat', 'selesai' => Konten::terbit()->where('jenis', 'halaman')->exists(), 'url' => KontenResource::getUrl('index')],
            ['label' => 'Manual Book untuk guru sudah diunggah', 'selesai' => ManualBook::exists(), 'url' => ManualBookResource::getUrl('index')],
        ];
    }

    public static function persiapanSelesai(): bool
    {
        return collect(self::persiapan())->every('selesai');
    }

    /** @return list<array{judul: string, rincian: ?string, url: string, tingkat: string}> */
    public static function tindakLanjut(): array
    {
        $tugas = [];

        $menunggu = User::where('status', 'menunggu')->orderBy('created_at')->get(['name']);
        if ($menunggu->isNotEmpty()) {
            $tugas[] = [
                'judul' => $menunggu->count().' pendaftar guru menunggu persetujuan',
                'rincian' => self::daftarNama($menunggu->pluck('name')),
                'url' => PenggunaResource::getUrl('index', ['tab' => 'menunggu']),
                'tingkat' => 'penting',
            ];
        }

        $pengaduan = Pengaduan::where('status', 'baru')->latest()->get(['isi']);
        if ($pengaduan->isNotEmpty()) {
            $tugas[] = [
                'judul' => $pengaduan->count().' pengaduan baru belum ditindaklanjuti',
                'rincian' => '"'.Str::limit($pengaduan->first()->isi, 90).'"',
                'url' => PengaduanResource::getUrl('index'),
                'tingkat' => 'penting',
            ];
        }

        $tanpaWali = Kelas::aktif()->whereNull('wali_kelas_id')->orderBy('tingkat')->orderBy('nama')->pluck('nama');
        if ($tanpaWali->isNotEmpty()) {
            $tugas[] = [
                'judul' => $tanpaWali->count().' kelas belum punya wali kelas',
                'rincian' => 'Kelas '.self::daftarNama($tanpaWali),
                'url' => KelasResource::getUrl('index'),
                'tingkat' => 'biasa',
            ];
        }

        $tanpaKelas = Murid::where('aktif', true)->whereDoesntHave('kelas', fn ($q) => $q->aktif())->count();
        if ($tanpaKelas) {
            $tugas[] = [
                'judul' => $tanpaKelas.' murid aktif belum dimasukkan ke kelas',
                'rincian' => 'Murid ini tidak muncul di daftar centang guru.',
                'url' => MuridResource::getUrl('index', ['kelas' => 'tanpa']),
                'tingkat' => 'biasa',
            ];
        }

        $tertinggal = collect(self::pencatatanKelas())->filter(fn ($k) => $k['hari_terlewat'] >= 3 && $k['jumlah_murid'] > 0);
        if ($tertinggal->isNotEmpty()) {
            $tugas[] = [
                'judul' => $tertinggal->count().' kelas belum mencatat kegiatan 3 hari kegiatan terakhir atau lebih',
                'rincian' => 'Kelas '.self::daftarNama($tertinggal->pluck('kelas')),
                'url' => '#pencatatan-kelas',
                'tingkat' => 'biasa',
            ];
        }

        $belumBerubah = KasusPembinaan::with('pemantauan', 'murid', 'kelas')
            ->where(fn ($q) => $q->where('status_akhir', 'belum')->orWhereNull('status_akhir'))
            ->whereHas('kelas', fn ($q) => $q->aktif())
            ->get()
            ->filter(fn ($k) => Rekap::statusTerkini($k) === 'belum' && ($k->status_akhir || $k->pemantauan->count() >= 4));
        if ($belumBerubah->isNotEmpty()) {
            $tugas[] = [
                'judul' => $belumBerubah->count().' murid belum berubah setelah 4 minggu pembinaan',
                'rincian' => self::daftarNama($belumBerubah->map(fn ($k) => $k->murid->nama.' ('.$k->kelas->nama.')')),
                'url' => KasusPembinaanResource::getUrl('index'),
                'tingkat' => 'biasa',
            ];
        }

        $cadangan = Pengaturan::ambil('cadangan_terakhir');
        $hariSejak = $cadangan ? (int) Carbon::parse($cadangan)->diffInDays(now()) : null;
        if ($hariSejak === null || $hariSejak > 30) {
            $tugas[] = [
                'judul' => $hariSejak === null ? 'Cadangan data belum pernah diunduh' : "Cadangan data terakhir diunduh {$hariSejak} hari lalu",
                'rincian' => 'Disarankan mengunduh cadangan setiap akhir bulan.',
                'url' => CadanganData::getUrl(),
                'tingkat' => 'biasa',
            ];
        }

        $ta = Rekap::tahunAjaranAktif();
        if ($ta && today()->diffInDays($ta->selesai, false) <= 30 && today()->lte($ta->selesai)) {
            $tugas[] = [
                'judul' => "Tahun ajaran {$ta->nama} berakhir ".$ta->selesai->translatedFormat('d F Y'),
                'rincian' => 'Siapkan tahun ajaran berikutnya lewat tombol "Mulai tahun ajaran baru".',
                'url' => TahunAjaranResource::getUrl('index'),
                'tingkat' => 'biasa',
            ];
        }

        return $tugas;
    }

    /**
     * Pencatatan kegiatan harian setiap kelas pada minggu berjalan.
     *
     * @return list<array{kelas: string, wali: ?string, jumlah_murid: int, terakhir: ?Carbon, minggu_ini: int, hari_minggu_ini: int, hari_terlewat: int}>
     */
    public static function pencatatanKelas(): array
    {
        return Rekap::ingat('pencatatan-kelas', function () {
            $hariMinggu = Rekap::hariKegiatan(today()->startOfWeek(), today());
            // Hari kegiatan sebelum hari ini (paling banyak 3 minggu ke belakang) untuk menghitung yang terlewat
            $hariLalu = Rekap::hariKegiatan(today()->subDays(21), today()->subDay())->reverse()->values();

            $kelasList = Kelas::aktif()->with('waliKelas')
                ->withCount(['murid as jumlah_murid' => fn ($q) => $q->where('murid.aktif', true)])
                ->orderBy('tingkat')->orderBy('nama')->get();

            $terakhir = KegiatanHarian::whereIn('kelas_id', $kelasList->pluck('id'))
                ->selectRaw('kelas_id, MAX(tanggal) as terakhir')->groupBy('kelas_id')->pluck('terakhir', 'kelas_id');
            $mingguIni = KegiatanHarian::whereIn('kelas_id', $kelasList->pluck('id'))
                ->whereIn('tanggal', $hariMinggu->map->toDateString())
                ->selectRaw('kelas_id, COUNT(*) as jml')->groupBy('kelas_id')->pluck('jml', 'kelas_id');

            return $kelasList->map(function (Kelas $k) use ($terakhir, $mingguIni, $hariMinggu, $hariLalu) {
                $akhir = isset($terakhir[$k->id]) ? Carbon::parse($terakhir[$k->id]) : null;

                return [
                    'kelas' => $k->nama,
                    'wali' => $k->waliKelas?->name,
                    'jumlah_murid' => (int) $k->jumlah_murid,
                    'terakhir' => $akhir,
                    'minggu_ini' => (int) ($mingguIni[$k->id] ?? 0),
                    'hari_minggu_ini' => $hariMinggu->count(),
                    'hari_terlewat' => $hariLalu->takeWhile(fn ($h) => ! $akhir || $h->gt($akhir))->count(),
                ];
            })->all();
        });
    }

    /** @return array{guru: int, murid: int, kelas: int} */
    public static function angka(): array
    {
        return [
            'guru' => User::guru()->where('status', 'aktif')->count(),
            'murid' => Murid::where('aktif', true)->count(),
            'kelas' => Kelas::aktif()->count(),
        ];
    }

    public static function teksTerakhir(?Carbon $tanggal): string
    {
        if (! $tanggal) {
            return 'Belum pernah';
        }

        $selisih = (int) $tanggal->copy()->startOfDay()->diffInDays(today());

        return match (true) {
            $selisih === 0 => 'Hari ini',
            $selisih === 1 => 'Kemarin',
            default => $selisih.' hari lalu',
        };
    }

    protected static function daftarNama(Collection $nama, int $batas = 4): string
    {
        $tampil = $nama->take($batas)->join(', ');

        return $nama->count() > $batas ? $tampil.', dan '.($nama->count() - $batas).' lainnya' : $tampil;
    }
}
