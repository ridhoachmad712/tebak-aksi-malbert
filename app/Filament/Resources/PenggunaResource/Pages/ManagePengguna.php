<?php

namespace App\Filament\Resources\PenggunaResource\Pages;

use App\Filament\Resources\PenggunaResource;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\Arr;

class ManagePengguna extends ManageRecords
{
    protected static string $resource = PenggunaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah pengguna')->using(function (array $data) {
                $wali = Arr::pull($data, 'kelas_wali_id');
                $user = User::create($data);
                PenggunaResource::simpanKelasWali($user, $wali);

                return $user;
            }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua'),
            'menunggu' => Tab::make('Menunggu persetujuan')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'menunggu'))
                ->badge(User::where('status', 'menunggu')->count() ?: null)->badgeColor('warning'),
            'guru' => Tab::make('Guru')->modifyQueryUsing(fn ($query) => $query->where('peran', 'guru')),
        ];
    }
}
