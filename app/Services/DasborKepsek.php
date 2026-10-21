<?php

namespace App\Services;

use App\Models\KaryaLiterasi;
use App\Models\KasusPembinaan;
use Illuminate\Support\Collection;

/** Data untuk dashboard kepala sekolah: hal yang perlu ditindak minggu ini dan kabar baik. */
class DasborKepsek
{
    /** Murid yang belum berubah setelah 4 minggu pembinaan, dari semua kasus kelas aktif. */
    public static function belumBerubah(): Collection
    {
        return Rekap::ingat('kasus-belum-berubah', fn () => KasusPembinaan::with(['pemantauan', 'murid', 'kelas', 'jenisPelanggaran', 'tindakanPembinaan'])
            ->where(fn ($q) => $q->where('status_akhir', 'belum')->orWhereNull('status_akhir'))
            ->whereHas('kelas', fn ($q) => $q->aktif())
            ->orderBy('tanggal')
            ->get()
            ->filter(fn ($k) => Rekap::statusTerkini($k) === 'belum' && ($k->status_akhir || $k->pemantauan->count() >= 4))
            ->values());
    }

    /** Pelanggaran tingkat berat yang dicatat dalam 7 hari terakhir. */
    public static function pelanggaranBerat(): Collection
    {
        return KasusPembinaan::with(['murid', 'kelas', 'jenisPelanggaran', 'tindakanPembinaan'])
            ->where('tingkat', 'berat')
            ->where('tanggal', '>=', today()->subDays(6)->toDateString())
            ->whereHas('kelas', fn ($q) => $q->aktif())
            ->latest('tanggal')
            ->get();
    }

    /** Kelas dengan kenaikan murid aktif terbesar dibanding bulan lalu (minimal 1 poin). */
    public static function kelasNaik(array $indikator, ?array $indikatorLalu): ?array
    {
        if (! $indikatorLalu) {
            return null;
        }
        $lalu = collect($indikatorLalu['per_kelas'])->keyBy(fn ($r) => $r['kelas']->id);

        return collect($indikator['per_kelas'])
            ->map(fn ($r) => $r + ['naik' => ($r['persen'] !== null && ($lalu[$r['kelas']->id]['persen'] ?? null) !== null) ? round($r['persen'] - $lalu[$r['kelas']->id]['persen'], 1) : null])
            ->filter(fn ($r) => $r['naik'] !== null && $r['naik'] >= 1)
            ->sortByDesc('naik')
            ->first();
    }

    public static function karyaTerbaru(int $jumlah = 4): Collection
    {
        return KaryaLiterasi::with(['kelas', 'jenisKarya', 'murid', 'guruPemilik'])
            ->whereHas('kelas', fn ($q) => $q->aktif())
            ->latest('tanggal')->latest('id')
            ->take($jumlah)
            ->get();
    }

    public static function sapaan(): string
    {
        return match (true) {
            now()->hour < 11 => 'Selamat pagi',
            now()->hour < 15 => 'Selamat siang',
            now()->hour < 18 => 'Selamat sore',
            default => 'Selamat malam',
        };
    }
}
