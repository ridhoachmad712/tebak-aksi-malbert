<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

// Menu publik yang sudah disimpan admin ditambah "Karya Literasi" (setelah Galeri) agar halaman barunya langsung terlihat
return new class extends Migration
{
    public function up(): void
    {
        $nilai = DB::table('pengaturan')->where('kunci', 'menu_publik')->value('nilai');
        $menu = $nilai ? json_decode($nilai, true) : null;
        // Menu bawaan sudah memuat Karya Literasi; batas menu di Tampilan Situs adalah 9
        if (! is_array($menu) || collect($menu)->contains('tujuan', 'karya') || count($menu) >= 9) {
            return;
        }

        $posisi = collect($menu)->search(fn ($m) => ($m['tujuan'] ?? null) === 'galeri');
        $posisi = $posisi === false ? count($menu) : $posisi + 1;
        array_splice($menu, $posisi, 0, [['tujuan' => 'karya', 'label' => 'Karya Literasi', 'tampil' => true]]);

        DB::table('pengaturan')->where('kunci', 'menu_publik')->update(['nilai' => json_encode($menu)]);
        Cache::forget('pengaturan');
    }

    public function down(): void
    {
        $nilai = DB::table('pengaturan')->where('kunci', 'menu_publik')->value('nilai');
        $menu = $nilai ? json_decode($nilai, true) : null;
        if (! is_array($menu)) {
            return;
        }

        $menu = array_values(array_filter($menu, fn ($m) => ($m['tujuan'] ?? null) !== 'karya'));
        DB::table('pengaturan')->where('kunci', 'menu_publik')->update(['nilai' => json_encode($menu)]);
        Cache::forget('pengaturan');
    }
};
