<?php

namespace App\Filament\Resources\KontenResource\Pages;

use App\Filament\Resources\KontenResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageKonten extends ManageRecords
{
    protected static string $resource = KontenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
