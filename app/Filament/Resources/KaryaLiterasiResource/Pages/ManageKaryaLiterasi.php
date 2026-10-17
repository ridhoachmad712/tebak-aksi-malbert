<?php

namespace App\Filament\Resources\KaryaLiterasiResource\Pages;

use App\Filament\Resources\KaryaLiterasiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageKaryaLiterasi extends ManageRecords
{
    protected static string $resource = KaryaLiterasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
