<?php

namespace App\Filament\Resources\TahunAjaranResource\Pages;

use App\Filament\Resources\TahunAjaranResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTahunAjaran extends ManageRecords
{
    protected static string $resource = TahunAjaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
