<?php

namespace App\Filament\Pages;

use App\Models\Pengaturan;
use App\Services\CadanganData as Cadangan;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use UnitEnum;

class CadanganData extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCircleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Cadangan Data';

    protected static ?string $title = 'Cadangan Data';

    protected static ?string $slug = 'cadangan';

    protected static ?int $navigationSort = 3;

    public function content(Schema $schema): Schema
    {
        $terakhir = Pengaturan::ambil('cadangan_terakhir');

        return $schema->components([
            Section::make('Unduh cadangan')
                ->description('Simpan cadangan secara rutin, misalnya setiap akhir bulan, di laptop sekolah atau Google Drive. Berkas berisi data murid, jadi simpan di tempat yang aman.')
                ->schema([
                    TextEntry::make('terakhir')->label('Cadangan data terakhir diunduh')
                        ->state($terakhir ? Carbon::parse($terakhir)->translatedFormat('l, d F Y H.i') : 'Belum pernah'),
                    Actions::make([
                        Action::make('unduhData')->label('Unduh data (.sql)')->icon(Heroicon::OutlinedArrowDownTray)
                            ->action(function () {
                                Pengaturan::simpan(['cadangan_terakhir' => now()->toDateTimeString()]);

                                return response()->streamDownload(
                                    fn () => Cadangan::tulisSql(function (string $teks) {
                                        echo $teks;
                                    }),
                                    'cadangan-tebak-aksi-'.now()->format('Y-m-d-His').'.sql',
                                    ['Content-Type' => 'application/sql; charset=utf-8'],
                                );
                            }),
                        Action::make('unduhBerkas')->label('Unduh foto dan berkas (.zip)')->icon(Heroicon::OutlinedPhoto)->color('gray')
                            ->action(fn () => response()->download(Cadangan::buatZipBerkas())->deleteFileAfterSend()),
                    ]),
                ]),
            Section::make('Cara memulihkan')->collapsed()->schema([
                TextEntry::make('panduan')->hiddenLabel()->state(
                    '1) Data: buka phpMyAdmin di hPanel Hostinger, pilih database aplikasi, buka tab Import, lalu pilih berkas .sql. '
                    .'Tabel lama akan diganti dengan isi cadangan. '
                    .'2) Foto dan berkas: ekstrak isi berkas .zip ke folder storage/app/public di server. '
                    .'Selain cadangan ini, aktifkan juga cadangan otomatis di hPanel (Files, Backups).'
                ),
            ]),
        ]);
    }
}
