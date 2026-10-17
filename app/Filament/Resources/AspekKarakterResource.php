<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AspekKarakterResource\Pages\ManageAspekKarakter;
use App\Models\AspekKarakter;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class AspekKarakterResource extends Resource
{
    protected static ?string $model = AspekKarakter::class;

    protected static ?string $slug = 'aspek-karakter';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|UnitEnum|null $navigationGroup = 'Data Master';

    protected static ?string $modelLabel = 'aspek karakter';

    protected static ?string $pluralModelLabel = 'Aspek Karakter';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nama')->required()->maxLength(255),
            TextInput::make('urutan')->numeric()->default(0)->helperText('Angka kecil tampil lebih dulu.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('urutan')
            ->reorderable('urutan')
            ->columns([
                TextColumn::make('nama')->searchable()->weight('bold'),
                TextColumn::make('jenis_pelanggaran_count')->counts('jenisPelanggaran')->label('Jenis pelanggaran terkait'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageAspekKarakter::route('/')];
    }
}
