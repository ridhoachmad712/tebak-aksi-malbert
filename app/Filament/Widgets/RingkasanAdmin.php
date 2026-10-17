<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\PengaduanResource;
use App\Filament\Resources\PenggunaResource;
use App\Models\KasusPembinaan;
use App\Models\KegiatanHarian;
use App\Models\Kelas;
use App\Models\Pengaduan;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\Rekap;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RingkasanAdmin extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $kelasAktif = Kelas::aktif()->count();
        $tercatat = KegiatanHarian::whereDate('tanggal', today())->count();
        $menunggu = User::where('status', 'menunggu')->count();
        $pengaduan = Pengaduan::where('status', 'baru')->count();

        return [
            Stat::make('Pendaftar menunggu persetujuan', $menunggu)
                ->description($menunggu ? 'Klik untuk meninjau' : 'Tidak ada')
                ->color($menunggu ? 'warning' : 'gray')->icon('heroicon-o-user-plus')
                ->url(PenggunaResource::getUrl('index', ['tab' => 'menunggu'])),
            Stat::make('Kegiatan harian tercatat hari ini', Rekap::hariKegiatanIni() ? "$tercatat / $kelasAktif kelas" : 'Bukan hari kegiatan')
                ->color('info')->icon('heroicon-o-check-circle'),
            Stat::make('Murid dalam pembinaan', KasusPembinaan::dalamPembinaan()->count())
                ->color('danger')->icon('heroicon-o-heart'),
            Stat::make('Pengaduan baru', $pengaduan)
                ->color($pengaduan ? 'warning' : 'gray')->icon('heroicon-o-chat-bubble-left-right')
                ->url(PengaduanResource::getUrl('index')),
            Stat::make('Tahun ajaran aktif', TahunAjaran::aktif()?->nama ?? 'Belum diatur')
                ->icon('heroicon-o-calendar'),
        ];
    }
}
