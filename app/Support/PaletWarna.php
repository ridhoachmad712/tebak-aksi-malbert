<?php

namespace App\Support;

use App\Models\Pengaturan;

/**
 * Palet warna yang bisa dipilih admin. Setiap palet sudah diuji kontras: tulisan putih di atas
 * warna aksen 700 dan tulisan aksen 800 di atas putih memenuhi standar baca WCAG AA (≥ 4,5 : 1).
 *
 * "aksen" dipakai untuk tindakan utama, menu aktif, dan tautan (token Tailwind `brand-*`).
 * "pendamping" dipakai untuk kolom Kemampuan Literasi agar berbeda dari Budaya Membaca (token `toska-*`).
 * Warna status capaian dan perkembangan pembinaan sengaja tidak ikut berubah.
 */
class PaletWarna
{
    protected const JINGGA = ['50' => '#fff7ed', '100' => '#ffedd5', '200' => '#fed7aa', '300' => '#fdba74', '500' => '#f97316', '600' => '#ea580c', '700' => '#c2410c', '800' => '#9a3412', '900' => '#7c2d12'];

    protected const TOSKA = ['50' => '#f0fdfa', '100' => '#ccfbf1', '200' => '#99f6e4', '300' => '#5eead4', '500' => '#14b8a6', '600' => '#0d9488', '700' => '#0f766e', '800' => '#115e59', '900' => '#134e4a'];

    protected const BIRU = ['50' => '#eff6ff', '100' => '#dbeafe', '200' => '#bfdbfe', '300' => '#93c5fd', '500' => '#3b82f6', '600' => '#2563eb', '700' => '#1d4ed8', '800' => '#1e40af', '900' => '#1e3a8a'];

    protected const HIJAU = ['50' => '#f0fdf4', '100' => '#dcfce7', '200' => '#bbf7d0', '300' => '#86efac', '500' => '#22c55e', '600' => '#16a34a', '700' => '#15803d', '800' => '#166534', '900' => '#14532d'];

    protected const MERAH_BATA = ['50' => '#fef2f2', '100' => '#fee2e2', '200' => '#fecaca', '300' => '#fca5a5', '500' => '#ef4444', '600' => '#dc2626', '700' => '#b91c1c', '800' => '#991b1b', '900' => '#7f1d1d'];

    protected const NILA = ['50' => '#eef2ff', '100' => '#e0e7ff', '200' => '#c7d2fe', '300' => '#a5b4fc', '500' => '#6366f1', '600' => '#4f46e5', '700' => '#4338ca', '800' => '#3730a3', '900' => '#312e81'];

    public const DAFTAR = [
        'jingga' => ['label' => 'Jingga', 'keterangan' => 'Hangat dan ceria (bawaan)', 'aksen' => self::JINGGA, 'pendamping' => self::TOSKA, 'filament' => 'Orange'],
        'toska' => ['label' => 'Toska', 'keterangan' => 'Segar dan tenang', 'aksen' => self::TOSKA, 'pendamping' => self::JINGGA, 'filament' => 'Teal'],
        'biru' => ['label' => 'Biru', 'keterangan' => 'Rapi dan resmi', 'aksen' => self::BIRU, 'pendamping' => self::TOSKA, 'filament' => 'Blue'],
        'hijau' => ['label' => 'Hijau', 'keterangan' => 'Alami dan teduh', 'aksen' => self::HIJAU, 'pendamping' => self::NILA, 'filament' => 'Green'],
        'merah_bata' => ['label' => 'Merah Bata', 'keterangan' => 'Tegas dan bersemangat', 'aksen' => self::MERAH_BATA, 'pendamping' => self::TOSKA, 'filament' => 'Red'],
    ];

    public static function aktif(): array
    {
        return self::DAFTAR[Pengaturan::ambil('tema_warna')] ?? self::DAFTAR['jingga'];
    }

    public static function opsi(): array
    {
        return array_map(fn ($p) => $p['label'], self::DAFTAR);
    }

    /** Deklarasi variabel CSS untuk palet terpilih, dipasang di <head> setiap tata letak. */
    public static function css(?string $kunci = null): string
    {
        $palet = $kunci ? (self::DAFTAR[$kunci] ?? self::DAFTAR['jingga']) : self::aktif();
        $baris = [];
        foreach ($palet['aksen'] as $tingkat => $warna) {
            $baris[] = "--aksen-{$tingkat}:{$warna}";
        }
        foreach ($palet['pendamping'] as $tingkat => $warna) {
            $baris[] = "--kedua-{$tingkat}:{$warna}";
        }

        return ':root{'.implode(';', $baris).'}';
    }

    /** Ikon bawaan TEBAK AKSI dalam warna palet terpilih, sebagai data URI untuk ikon tab browser. */
    public static function ikonDataUri(): string
    {
        $p = self::aktif()['aksen'];
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40"><rect width="40" height="40" rx="9" fill="'.$p['700'].'"/>'
            .'<path d="M8 12.5c3.8-1.3 8-1 12 1.4v16c-4-2.4-8.2-2.7-12-1.4z" fill="#fff"/>'
            .'<path d="M32 12.5c-3.8-1.3-8-1-12 1.4v16c4-2.4 8.2-2.7 12-1.4z" fill="'.$p['100'].'"/></svg>';

        return 'data:image/svg+xml,'.rawurlencode($svg);
    }
}
