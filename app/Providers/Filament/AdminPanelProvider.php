<?php

namespace App\Providers\Filament;

use App\Models\Pengaturan;
use App\Support\PaletWarna;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        // Login memakai halaman /masuk milik aplikasi, bukan login bawaan Filament
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('TEBAK AKSI · Admin')
            ->brandLogo(fn () => view('filament.brand'))
            ->favicon(fn () => ($logo = Pengaturan::ambil('logo_sekolah')) ? Storage::disk('public')->url($logo) : PaletWarna::ikonDataUri())
            ->colors(fn () => [
                'primary' => constant(Color::class.'::'.PaletWarna::aktif()['filament']),
                'info' => Color::Teal,
            ])
            ->font('Nunito')
            ->darkMode(false)
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->navigationGroups([
                // Tautan yang keluar dari panel admin dikumpulkan terpisah dan dibuka di tab baru
                NavigationGroup::make('Buka Aplikasi'),
                NavigationGroup::make('Pengguna & Kelas'),
                NavigationGroup::make('Catatan Guru'),
                NavigationGroup::make('Data Master'),
                NavigationGroup::make('Halaman Publik'),
                NavigationGroup::make('Pengaturan'),
            ])
            ->navigationItems([
                NavigationItem::make('Monev')
                    ->url(fn () => route('monev'), shouldOpenInNewTab: true)
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->group('Buka Aplikasi')
                    ->sort(1),
                NavigationItem::make('Halaman Guru')
                    ->url(fn () => route('guru.kegiatan'), shouldOpenInNewTab: true)
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->group('Buka Aplikasi')
                    ->sort(2),
                NavigationItem::make('Halaman Publik')
                    ->url(fn () => route('beranda'), shouldOpenInNewTab: true)
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->group('Buka Aplikasi')
                    ->sort(3),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
