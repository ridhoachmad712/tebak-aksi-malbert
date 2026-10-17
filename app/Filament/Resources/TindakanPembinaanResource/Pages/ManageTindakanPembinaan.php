<?php

namespace App\Filament\Resources\TindakanPembinaanResource\Pages;

use App\Filament\Resources\TindakanPembinaanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTindakanPembinaan extends ManageRecords
{
    protected static string $resource = TindakanPembinaanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
