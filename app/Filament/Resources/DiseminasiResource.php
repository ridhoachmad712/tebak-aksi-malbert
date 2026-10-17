<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiseminasiResource\Pages\ManageDiseminasi;
use App\Models\Diseminasi;
use App\Services\SimpanFoto;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class DiseminasiResource extends Resource
{
    protected static ?string $model = Diseminasi::class;

    protected static ?string $slug = 'diseminasi';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|UnitEnum|null $navigationGroup = 'Halaman Publik';

    protected static ?string $modelLabel = 'kegiatan diseminasi';

    protected static ?string $pluralModelLabel = 'Diseminasi';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('judul')->required()->maxLength(255)->columnSpanFull(),
            DatePicker::make('tanggal')->required()->native(false)->displayFormat('d/m/Y')->default(today()),
            TextInput::make('tempat')->maxLength(255),
            Textarea::make('keterangan')->rows(3)->columnSpanFull(),
            FileUpload::make('foto_baru')->label('Tambah foto dokumentasi')->image()->multiple()->disk('local')->directory('unggah-sementara')
                ->maxSize(SimpanFoto::MAKS_KB)->columnSpanFull()
                ->helperText('Foto yang sudah tersimpan dapat dikelola di menu Galeri Dokumentasi.'),
            Toggle::make('terbit')->label('Tampilkan di halaman publik')->default(true),
        ]);
    }

    /** Menyimpan data diseminasi beserta foto baru yang diunggah. */
    public static function simpan(?Diseminasi $record, array $data): Diseminasi
    {
        $foto = $data['foto_baru'] ?? [];
        unset($data['foto_baru']);

        $record ? $record->update($data) : $record = Diseminasi::create($data);

        foreach ($foto as $path) {
            $penuh = Storage::disk('local')->path($path);
            $record->foto()->create([
                'path' => SimpanFoto::simpan(new UploadedFile($penuh, basename($penuh), test: true), 'diseminasi'),
                'user_id' => auth()->id(),
            ]);
            Storage::disk('local')->delete($path);
        }

        return $record;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('tanggal', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->withCount('foto'))
            ->columns([
                TextColumn::make('judul')->weight('bold')->searchable()->wrap()->description(fn (Diseminasi $d) => $d->tempat),
                TextColumn::make('tanggal')->date('d/m/Y')->sortable(),
                TextColumn::make('foto_count')->label('Foto'),
                ToggleColumn::make('terbit')->label('Tampil'),
            ])
            ->recordActions([
                EditAction::make()->using(fn (Diseminasi $record, array $data) => static::simpan($record, $data)),
                DeleteAction::make()->before(fn (Diseminasi $record) => $record->foto->each->delete()),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageDiseminasi::route('/')];
    }
}
