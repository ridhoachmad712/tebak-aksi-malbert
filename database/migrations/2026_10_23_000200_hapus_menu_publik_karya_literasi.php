<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

// Karya Literasi dipindah ke menu admin; menu publik yang sempat ditambahkan dibersihkan
return new class extends Migration
{
    public function up(): void
    {
        $nilai = DB::table('pengaturan')->where('kunci', 'menu_publik')->value('nilai');
        $menu = $nilai ? json_decode($nilai, true) : null;
        if (! is_array($menu) || ! collect($menu)->contains('tujuan', 'karya')) {
            return;
        }

        $menu = array_values(array_filter($menu, fn ($m) => ($m['tujuan'] ?? null) !== 'karya'));
        DB::table('pengaturan')->where('kunci', 'menu_publik')->update(['nilai' => json_encode($menu)]);
        Cache::forget('pengaturan');
    }

    public function down(): void
    {
        // Tidak dikembalikan: halaman publik Karya Literasi sudah tidak ada
    }
};
