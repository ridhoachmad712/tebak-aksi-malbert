<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TindakanPembinaanResource\Pages\ManageTindakanPembinaan;
use App\Models\TindakanPembinaan;
use BackedEnum;
use Filament\Actions\DeleteAction;
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

class TindakanPembinaanResource extends Resource
{
    protected static ?string $model = TindakanPembinaan::class;

    protected static ?string $slug = 'tindakan-pembinaan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHandRaised;

    protected static string|UnitEnum|null $navigationGroup = 'Data Master';

    protected static ?string $modelLabel = 'tindakan pembinaan';

    protected static ?string $pluralModelLabel = 'Tindakan Pembinaan';

    protected static ?int $navigationSort = 3;

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
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageTindakanPembinaan::route('/')];
    }
}
