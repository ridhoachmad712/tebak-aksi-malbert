<?php

namespace App\Filament\Resources\DiseminasiResource\Pages;

use App\Filament\Resources\DiseminasiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageDiseminasi extends ManageRecords
{
    protected static string $resource = DiseminasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->using(fn (array $data) => DiseminasiResource::simpan(null, $data)),
        ];
    }
}
