<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KontenResource\Pages\ManageKonten;
use App\Models\Konten;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class KontenResource extends Resource
{
    protected static ?string $model = Konten::class;

    protected static ?string $slug = 'konten';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|UnitEnum|null $navigationGroup = 'Halaman Publik';

    protected static ?string $modelLabel = 'konten';

    protected static ?string $pluralModelLabel = 'Halaman & Berita';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Grid::make(2)->schema([
                Select::make('jenis')->options(Konten::JENIS)->default('berita')->required()->live()
                    ->helperText('Halaman: konten tetap (misalnya Profil). Berita: kabar kegiatan.'),
                TextInput::make('judul')->required()->maxLength(255)->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Set $set, $record) => $record ? null : $set('slug', Str::slug($state))),
                TextInput::make('slug')->label('Alamat (slug)')->required()->unique(ignoreRecord: true)->maxLength(255)->prefix('/halaman/'),
                DateTimePicker::make('terbit_pada')->label('Tanggal terbit')->default(now())->native(false),
            ]),
            Textarea::make('ringkasan')->rows(2)->maxLength(500),
            FileUpload::make('gambar')->label('Gambar utama')->image()->disk('public')->directory('konten')->maxSize(4096),
            RichEditor::make('isi')->toolbarButtons([['bold', 'italic', 'underline', 'link'], ['h2', 'h3', 'blockquote', 'bulletList', 'orderedList'], ['undo', 'redo']]),
            Grid::make(1)->schema([
                Toggle::make('terbit')->label('Terbitkan')->default(true),
            ]),
            Placeholder::make('info_menu')->hiddenLabel()->visible(fn (Get $get) => $get('jenis') === 'halaman')
                ->content('Untuk menampilkan halaman ini di menu atas, tambahkan di Tampilan Situs, tab Menu.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('terbit_pada', 'desc')
            ->columns([
                TextColumn::make('judul')->searchable()->weight('bold')->wrap()->description(fn (Konten $k) => '/halaman/'.$k->slug),
                TextColumn::make('jenis')->badge()->formatStateUsing(fn ($state) => Konten::JENIS[$state]),
                TextColumn::make('terbit_pada')->label('Terbit')->date('d/m/Y')->sortable(),
                ToggleColumn::make('terbit'),
            ])
            ->filters([SelectFilter::make('jenis')->options(Konten::JENIS)])
            ->recordActions([
                Action::make('lihat')->icon(Heroicon::OutlinedEye)->color('gray')->url(fn (Konten $k) => route('halaman', $k->slug), shouldOpenInNewTab: true),
                EditAction::make()->slideOver(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageKonten::route('/')];
    }
}
