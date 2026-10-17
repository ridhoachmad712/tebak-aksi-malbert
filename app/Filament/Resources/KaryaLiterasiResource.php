<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KaryaLiterasiResource\Pages\ManageKaryaLiterasi;
use App\Models\KaryaLiterasi;
use App\Models\Kelas;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class KaryaLiterasiResource extends Resource
{
    protected static ?string $model = KaryaLiterasi::class;

    protected static ?string $slug = 'karya-literasi';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaintBrush;

    protected static string|UnitEnum|null $navigationGroup = 'Catatan Guru';

    protected static ?string $modelLabel = 'karya literasi';

    protected static ?string $pluralModelLabel = 'Karya Literasi';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('tanggal', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with(['murid', 'guruPemilik', 'kelas', 'jenisKarya', 'foto']))
            ->columns([
                ImageColumn::make('foto_pertama')->label('')->disk('public')->state(fn (KaryaLiterasi $k) => $k->foto->first()?->path)->imageHeight(48),
                TextColumn::make('judul')->searchable()->weight('bold')->description(fn (KaryaLiterasi $k) => $k->namaJenis()),
                TextColumn::make('pemilik')->state(fn (KaryaLiterasi $k) => $k->namaPemilik())->wrap(),
                TextColumn::make('kelas.nama')->label('Kelas')->placeholder('-'),
                TextColumn::make('tanggal')->date('d/m/Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('kelas_id')->label('Kelas')->options(fn () => Kelas::aktif()->orderBy('tingkat')->orderBy('nama')->pluck('nama', 'id')),
                SelectFilter::make('jenis_karya_id')->label('Jenis karya')->relationship('jenisKarya', 'nama'),
            ])
            ->recordActions([
                Action::make('buka')->label('Buka')->icon(Heroicon::OutlinedArrowTopRightOnSquare)->url(fn (KaryaLiterasi $k) => route('guru.karya.ubah', $k)),
                DeleteAction::make()->before(fn (KaryaLiterasi $record) => $record->foto->each->delete()),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageKaryaLiterasi::route('/')];
    }
}
