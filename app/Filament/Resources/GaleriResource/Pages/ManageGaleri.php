<?php

namespace App\Filament\Resources\GaleriResource\Pages;

use App\Filament\Resources\GaleriResource;
use App\Models\Foto;
use App\Services\SimpanFoto;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ManageGaleri extends ManageRecords
{
    protected static string $resource = GaleriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('unggah')->label('Unggah foto')->icon(Heroicon::OutlinedArrowUpTray)
                ->schema([
                    FileUpload::make('berkas')->label('Foto')->image()->multiple()->disk('local')->directory('unggah-sementara')
                        ->maxSize(SimpanFoto::MAKS_KB)->required(),
                    TextInput::make('keterangan')->maxLength(255),
                ])
                ->action(function (array $data) {
                    foreach ($data['berkas'] as $path) {
                        $penuh = Storage::disk('local')->path($path);
                        Foto::create([
                            'path' => SimpanFoto::simpan(new UploadedFile($penuh, basename($penuh), test: true), 'galeri'),
                            'keterangan' => $data['keterangan'] ?? null,
                            'tampil_galeri' => true,
                            'user_id' => auth()->id(),
                        ]);
                        Storage::disk('local')->delete($path);
                    }
                    Notification::make()->success()->title(count($data['berkas']).' foto ditambahkan ke galeri')->send();
                }),
        ];
    }
}
