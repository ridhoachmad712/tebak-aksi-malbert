<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JenisPelanggaranResource\Pages\ManageJenisPelanggaran;
use App\Models\JenisPelanggaran;
use BackedEnum;
use Filament\Actions\DeleteAction;
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

class JenisPelanggaranResource extends Resource
{
    protected static ?string $model = JenisPelanggaran::class;

    protected static ?string $slug = 'jenis-pelanggaran';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static string|UnitEnum|null $navigationGroup = 'Data Master';

    protected static ?string $modelLabel = 'jenis pelanggaran';

    protected static ?string $pluralModelLabel = 'Jenis Pelanggaran';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nama')->required()->maxLength(255)->columnSpanFull(),
            Select::make('aspek_karakter_id')->label('Aspek karakter')
                ->relationship('aspekKarakter', 'nama')->preload()
                ->helperText('Dipakai Monev untuk menunjukkan aspek karakter yang paling perlu dibina.'),
            TextInput::make('urutan')->numeric()->default(0),
            Toggle::make('aktif')->default(true)->helperText('Nonaktifkan agar tidak muncul di pilihan guru.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('urutan')
            ->reorderable('urutan')
            ->columns([
                TextColumn::make('nama')->searchable()->weight('bold')->wrap(),
                TextColumn::make('aspekKarakter.nama')->label('Aspek karakter')->badge()->color('info')->placeholder('-'),
                ToggleColumn::make('aktif'),
            ])
            ->filters([SelectFilter::make('aspek_karakter_id')->label('Aspek karakter')->relationship('aspekKarakter', 'nama')])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageJenisPelanggaran::route('/')];
    }
}
