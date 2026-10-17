<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JenisKegiatanResource\Pages\ManageJenisKegiatan;
use App\Filament\Support\HapusAman;
use App\Models\JenisKegiatan;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class JenisKegiatanResource extends Resource
{
    protected static ?string $model = JenisKegiatan::class;

    protected static ?string $slug = 'jenis-kegiatan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Data Master';

    protected static ?string $modelLabel = 'jenis kegiatan';

    protected static ?string $pluralModelLabel = 'Jenis Kegiatan';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nama')->required()->maxLength(255)->columnSpanFull(),
            TextInput::make('urutan')->numeric()->default(0),
            Toggle::make('aktif')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('urutan')
            ->reorderable('urutan')
            ->columns([
                TextColumn::make('nama')->searchable()->weight('bold'),
                ToggleColumn::make('aktif'),
            ])
            ->recordActions([EditAction::make(), HapusAman::aksi()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageJenisKegiatan::route('/')];
    }
}
