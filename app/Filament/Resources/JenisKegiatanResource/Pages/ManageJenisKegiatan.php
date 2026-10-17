<?php

namespace App\Filament\Resources\JenisKegiatanResource\Pages;

use App\Filament\Resources\JenisKegiatanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageJenisKegiatan extends ManageRecords
{
    protected static string $resource = JenisKegiatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
