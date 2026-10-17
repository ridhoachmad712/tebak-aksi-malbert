<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HariLiburResource\Pages\ManageHariLibur;
use App\Models\HariLibur;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class HariLiburResource extends Resource
{
    protected static ?string $model = HariLibur::class;

    protected static ?string $slug = 'hari-libur';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?string $modelLabel = 'hari libur';

    protected static ?string $pluralModelLabel = 'Kalender (Hari Libur)';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('tanggal')->required()->unique(ignoreRecord: true)->native(false)->displayFormat('d/m/Y'),
            TextInput::make('keterangan')->required()->maxLength(255)->placeholder('Contoh: Libur semester'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->description('Hari Senin–Jumat yang ditandai di sini tidak dihitung sebagai hari kegiatan Teras Baca.')
            ->defaultSort('tanggal', 'desc')
            ->columns([
                TextColumn::make('tanggal')->date('l, d F Y')->sortable()->weight('bold'),
                TextColumn::make('keterangan')->searchable(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageHariLibur::route('/')];
    }
}
