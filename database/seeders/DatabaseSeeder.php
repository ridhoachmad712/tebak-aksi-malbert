<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(DataAwalSeeder::class);

        // Data contoh hanya untuk pengembangan/uji coba, tidak pernah di server produksi
        if (! app()->isProduction()) {
            $this->call(DataContohSeeder::class);
        }
    }
}
