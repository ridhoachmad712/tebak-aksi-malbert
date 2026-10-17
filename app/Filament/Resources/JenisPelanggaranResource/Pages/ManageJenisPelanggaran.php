<?php

namespace App\Filament\Resources\JenisPelanggaranResource\Pages;

use App\Filament\Resources\JenisPelanggaranResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageJenisPelanggaran extends ManageRecords
{
    protected static string $resource = JenisPelanggaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
