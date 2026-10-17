<?php

namespace App\Filament\Resources\ManualBookResource\Pages;

use App\Filament\Resources\ManualBookResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageManualBook extends ManageRecords
{
    protected static string $resource = ManualBookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
