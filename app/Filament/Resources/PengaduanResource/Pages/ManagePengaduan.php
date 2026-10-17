<?php

namespace App\Filament\Resources\PengaduanResource\Pages;

use App\Filament\Resources\PengaduanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePengaduan extends ManageRecords
{
    protected static string $resource = PengaduanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
