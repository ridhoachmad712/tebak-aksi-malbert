<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KaryaLiterasiResource\Pages\ManageKaryaLiterasi;
use App\Models\KaryaLiterasi;
use App\Models\Kelas;
use App\Services\Rekap;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
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
                // Ketuk foto untuk membuka ukuran penuh di tab baru (bisa diperbesar)
                ImageColumn::make('foto_pertama')->label('Foto')->disk('public')->state(fn (KaryaLiterasi $k) => $k->foto->first()?->path)->imageHeight(80)
                    ->url(fn (KaryaLiterasi $k) => $k->foto->first()?->url(), shouldOpenInNewTab: true)
                    ->placeholder('Belum ada foto'),
                TextColumn::make('judul')->searchable()->weight('bold')->description(fn (KaryaLiterasi $k) => $k->namaJenis())->wrap(),
                TextColumn::make('pemilik')->label('Nama murid / pemilik')->state(fn (KaryaLiterasi $k) => $k->namaPemilik())->wrap()
                    ->searchable(query: fn (Builder $query, string $search) => $query->where(fn ($q) => $q
                        ->whereHas('murid', fn ($m) => $m->where('nama', 'like', "%{$search}%"))
                        ->orWhereHas('guruPemilik', fn ($u) => $u->where('name', 'like', "%{$search}%")))),
                TextColumn::make('kelas.nama')->label('Kelas')->placeholder('-'),
                TextColumn::make('tanggal')->date('d/m/Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('bulan')->label('Bulan')->options(fn () => Rekap::daftarBulan())
                    ->query(function (Builder $query, array $data) {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }
                        $awal = Carbon::createFromFormat('!Y-m', $data['value']);

                        return $query->whereBetween('tanggal', [$awal->toDateString(), $awal->copy()->endOfMonth()->toDateString()]);
                    }),
                SelectFilter::make('kelas_id')->label('Kelas')->options(fn () => Kelas::aktif()->orderBy('tingkat')->orderBy('nama')->pluck('nama', 'id')->map(fn ($n) => 'Kelas '.$n)),
                SelectFilter::make('pemilik')->label('Pemilik')->options(['murid' => 'Karya murid', 'guru' => 'Karya guru'])
                    ->query(fn (Builder $query, array $data) => match ($data['value'] ?? null) {
                        'murid' => $query->whereNull('guru_pemilik_id'),
                        'guru' => $query->whereNotNull('guru_pemilik_id'),
                        default => $query,
                    }),
                SelectFilter::make('jenis_karya_id')->label('Jenis karya')->relationship('jenisKarya', 'nama'),
            ])
            ->filtersFormColumns(2)
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
