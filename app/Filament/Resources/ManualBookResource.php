<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ManualBookResource\Pages\ManageManualBook;
use App\Models\ManualBook;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ManualBookResource extends Resource
{
    protected static ?string $model = ManualBook::class;

    protected static ?string $slug = 'manual-book';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|UnitEnum|null $navigationGroup = 'Halaman Publik';

    protected static ?string $modelLabel = 'manual book';

    protected static ?string $pluralModelLabel = 'Manual Book';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('judul')->required()->maxLength(255)->columnSpanFull(),
            Textarea::make('deskripsi')->rows(2)->columnSpanFull(),
            FileUpload::make('file')->label('Berkas PDF')->disk('public')->directory('manual')
                ->acceptedFileTypes(['application/pdf'])->maxSize(20480)->required()->columnSpanFull(),
            Select::make('untuk')->label('Dapat dilihat oleh')->options(ManualBook::UNTUK)->default('publik')->required(),
            TextInput::make('urutan')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('urutan')
            ->reorderable('urutan')
            ->columns([
                TextColumn::make('judul')->weight('bold')->searchable()->description(fn (ManualBook $m) => $m->deskripsi),
                TextColumn::make('untuk')->badge()->formatStateUsing(fn ($state) => ManualBook::UNTUK[$state]),
                TextColumn::make('file')->label('Berkas')->formatStateUsing(fn () => 'Buka PDF')->url(fn (ManualBook $m) => $m->urlFile(), shouldOpenInNewTab: true)->color('primary'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageManualBook::route('/')];
    }
}
