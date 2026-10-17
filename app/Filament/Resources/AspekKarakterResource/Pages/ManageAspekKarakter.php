<?php

namespace App\Filament\Resources\AspekKarakterResource\Pages;

use App\Filament\Resources\AspekKarakterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAspekKarakter extends ManageRecords
{
    protected static string $resource = AspekKarakterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
