<?php

namespace App\Providers;

use ArrayObject;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Simpanan hasil perhitungan Rekap, dibuang setiap permintaan baru
        $this->app->scoped('rekap.simpanan', fn () => new ArrayObject);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
