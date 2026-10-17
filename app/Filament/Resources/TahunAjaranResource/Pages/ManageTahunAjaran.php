<?php

namespace App\Filament\Resources\TahunAjaranResource\Pages;

use App\Filament\Resources\TahunAjaranResource;
use App\Models\TahunAjaran;
use App\Services\TahunAjaranBaru;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;

class ManageTahunAjaran extends ManageRecords
{
    protected static string $resource = TahunAjaranResource::class;

    protected function getHeaderActions(): array
    {
        $aktif = TahunAjaran::aktif();
        $tahun = $aktif ? $aktif->mulai->year + 1 : today()->year;

        return [
            Action::make('tahunAjaranBaru')->label('Mulai tahun ajaran baru')->icon(Heroicon::OutlinedArrowPathRoundedSquare)
                ->visible((bool) $aktif)
                ->modalHeading('Mulai tahun ajaran baru')
                ->modalDescription($aktif ? "Kelas dan murid diambil dari tahun ajaran {$aktif->nama}. Catatan tahun ajaran lama tetap tersimpan." : null)
                ->schema([
                    TextInput::make('nama')->required()->default($tahun.'/'.($tahun + 1))->unique('tahun_ajaran', 'nama'),
                    DatePicker::make('mulai')->required()->native(false)->displayFormat('d/m/Y')->default("$tahun-07-13"),
                    DatePicker::make('selesai')->required()->native(false)->displayFormat('d/m/Y')->default(($tahun + 1).'-06-30')->after('mulai'),
                    Toggle::make('salin_kelas')->label('Salin daftar kelas (1A, 2A, …)')->default(true)->live(),
                    Toggle::make('salin_guru')->label('Wali kelas dan guru mapel tetap di kelas yang sama')->default(true)
                        ->visible(fn (Get $get) => $get('salin_kelas')),
                    Toggle::make('naikkan_murid')->label('Naikkan murid ke tingkat berikutnya (4A → 5A)')->default(true)
                        ->visible(fn (Get $get) => $get('salin_kelas'))->live(),
                    Toggle::make('luluskan_kelas_6')->label('Nonaktifkan murid kelas VI (lulus)')->default(true)
                        ->visible(fn (Get $get) => $get('salin_kelas') && $get('naikkan_murid')),
                    Toggle::make('aktifkan')->label('Langsung jadikan tahun ajaran aktif')->default(true)
                        ->helperText('Guru akan langsung melihat kelas di tahun ajaran baru. Murid baru kelas I ditambahkan lewat menu Murid.'),
                ])
                ->action(function (array $data) use ($aktif) {
                    $data += ['salin_guru' => false, 'naikkan_murid' => false, 'luluskan_kelas_6' => false];
                    $hasil = TahunAjaranBaru::jalankan($aktif, $data);
                    Notification::make()->success()->persistent()
                        ->title("Tahun ajaran {$hasil['tahun_ajaran']->nama} dibuat")
                        ->body("{$hasil['kelas']} kelas disalin, {$hasil['dinaikkan']} murid naik kelas, {$hasil['lulus']} murid kelas VI dinonaktifkan.")
                        ->send();
                }),
            CreateAction::make()->label('Tambah manual'),
        ];
    }
}
