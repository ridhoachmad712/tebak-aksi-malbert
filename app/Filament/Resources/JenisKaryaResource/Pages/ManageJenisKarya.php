<?php

namespace App\Filament\Resources\JenisKaryaResource\Pages;

use App\Filament\Resources\JenisKaryaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageJenisKarya extends ManageRecords
{
    protected static string $resource = JenisKaryaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
