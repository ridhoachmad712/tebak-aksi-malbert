<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JenisKaryaResource\Pages\ManageJenisKarya;
use App\Filament\Support\HapusAman;
use App\Models\JenisKarya;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class JenisKaryaResource extends Resource
{
    protected static ?string $model = JenisKarya::class;

    protected static ?string $slug = 'jenis-karya';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaintBrush;

    protected static string|UnitEnum|null $navigationGroup = 'Data Master';

    protected static ?string $modelLabel = 'jenis karya';

    protected static ?string $pluralModelLabel = 'Jenis Karya';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nama')->required()->maxLength(255),
            Select::make('jenjang')->options(JenisKarya::JENJANG)->default('semua')->required(),
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
                TextColumn::make('jenjang')->formatStateUsing(fn ($state) => JenisKarya::JENJANG[$state] ?? $state)->badge(),
                ToggleColumn::make('aktif'),
            ])
            ->filters([SelectFilter::make('jenjang')->options(JenisKarya::JENJANG)])
            ->recordActions([EditAction::make(), HapusAman::aksi()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageJenisKarya::route('/')];
    }
}
