<?php

namespace App\Filament\Resources\HariLiburResource\Pages;

use App\Filament\Resources\HariLiburResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageHariLibur extends ManageRecords
{
    protected static string $resource = HariLiburResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
