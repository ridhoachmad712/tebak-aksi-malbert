<?php

namespace App\Filament\Resources\KasusPembinaanResource\Pages;

use App\Filament\Resources\KasusPembinaanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageKasusPembinaan extends ManageRecords
{
    protected static string $resource = KasusPembinaanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
