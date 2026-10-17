<?php

namespace App\Support;

/** Tiga halaman yang dapat dibuka admin, dipakai di halaman pilihan setelah masuk dan tombol pindah halaman. */
class HalamanAdmin
{
    /** @return list<array{kunci: string, label: string, keterangan: string, url: string, ikon: string}> */
    public static function daftar(): array
    {
        return [
            ['kunci' => 'admin', 'label' => 'Halaman Admin', 'keterangan' => 'Kelola guru, murid, kelas, pengaturan, dan konten situs.', 'url' => url('/admin'), 'ikon' => 'heroicon-o-cog-6-tooth'],
            ['kunci' => 'monev', 'label' => 'Halaman Monev', 'keterangan' => 'Lihat capaian program, peta kelas, dan cetak laporan.', 'url' => route('monev'), 'ikon' => 'heroicon-o-presentation-chart-line'],
            ['kunci' => 'guru', 'label' => 'Halaman Guru', 'keterangan' => 'Buka catatan harian, pembinaan, dan karya setiap kelas untuk koreksi.', 'url' => route('guru.beranda'), 'ikon' => 'heroicon-o-check-circle'],
        ];
    }

    /** Halaman yang sedang dibuka, berdasarkan rute. */
    public static function aktif(): string
    {
        return match (true) {
            request()->is('admin', 'admin/*') => 'admin',
            request()->routeIs('monev*') => 'monev',
            default => 'guru',
        };
    }
}
