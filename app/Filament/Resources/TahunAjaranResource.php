<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TahunAjaranResource\Pages\ManageTahunAjaran;
use App\Filament\Support\HapusAman;
use App\Models\TahunAjaran;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class TahunAjaranResource extends Resource
{
    protected static ?string $model = TahunAjaran::class;

    protected static ?string $slug = 'tahun-ajaran';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static string|UnitEnum|null $navigationGroup = 'Pengguna & Kelas';

    protected static ?string $modelLabel = 'tahun ajaran';

    protected static ?string $pluralModelLabel = 'Tahun Ajaran';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nama')->required()->placeholder('2026/2027')->maxLength(20),
            Toggle::make('aktif')->helperText('Hanya satu tahun ajaran yang aktif. Kelas di tahun ajaran aktif yang dipakai guru.'),
            DatePicker::make('mulai')->required()->native(false)->displayFormat('d/m/Y'),
            DatePicker::make('selesai')->required()->native(false)->displayFormat('d/m/Y')->after('mulai'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('mulai', 'desc')
            ->columns([
                TextColumn::make('nama')->weight('bold'),
                TextColumn::make('mulai')->date('d/m/Y'),
                TextColumn::make('selesai')->date('d/m/Y'),
                TextColumn::make('kelas_count')->counts('kelas')->label('Jumlah kelas'),
                IconColumn::make('aktif')->boolean(),
            ])
            ->recordActions([
                Action::make('aktifkan')->label('Jadikan aktif')->icon(Heroicon::OutlinedCheckCircle)
                    ->visible(fn (TahunAjaran $r) => ! $r->aktif)->requiresConfirmation()
                    ->action(fn (TahunAjaran $r) => $r->update(['aktif' => true])),
                EditAction::make(),
                HapusAman::aksi(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageTahunAjaran::route('/')];
    }
}
