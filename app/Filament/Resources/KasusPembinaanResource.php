<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KasusPembinaanResource\Pages\ManageKasusPembinaan;
use App\Models\KasusPembinaan;
use App\Models\Kelas;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class KasusPembinaanResource extends Resource
{
    protected static ?string $model = KasusPembinaan::class;

    protected static ?string $slug = 'kasus-pembinaan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static string|UnitEnum|null $navigationGroup = 'Catatan Guru';

    protected static ?string $modelLabel = 'kasus pembinaan';

    protected static ?string $pluralModelLabel = 'Kasus Pembinaan';

    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('tanggal', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with(['murid', 'kelas', 'jenisPelanggaran', 'tindakanPembinaan', 'pemantauan', 'pencatat']))
            ->columns([
                TextColumn::make('tanggal')->date('d/m/Y')->sortable(),
                TextColumn::make('murid.nama')->label('Murid')->searchable()->weight('bold')->description(fn (KasusPembinaan $k) => 'Kelas '.$k->kelas->nama),
                TextColumn::make('pelanggaran')->state(fn (KasusPembinaan $k) => $k->namaPelanggaran())->wrap(),
                TextColumn::make('tingkat')->badge()->formatStateUsing(fn ($state) => KasusPembinaan::TINGKAT[$state])
                    ->color(fn ($state) => match ($state) { 'berat' => 'danger', 'sedang' => 'warning', default => 'gray' }),
                TextColumn::make('pemantauan_ringkas')->label('Minggu 1–4')
                    ->state(fn (KasusPembinaan $k) => collect(range(1, 4))->map(fn ($m) => ($p = $k->pemantauan->firstWhere('minggu', $m)) ? KasusPembinaan::PERKEMBANGAN[$p->tingkat]['ikon'] : '·')->join(' ')),
                TextColumn::make('status')->state(fn (KasusPembinaan $k) => $k->labelStatus()),
                TextColumn::make('pencatat.name')->label('Dicatat oleh')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('kelas_id')->label('Kelas')->options(fn () => Kelas::aktif()->orderBy('tingkat')->orderBy('nama')->pluck('nama', 'id')),
                SelectFilter::make('tingkat')->options(KasusPembinaan::TINGKAT),
                SelectFilter::make('status')->options(['proses' => 'Dalam Pembinaan'] + collect(KasusPembinaan::PERKEMBANGAN)->map(fn ($p) => $p['label'])->all())
                    ->query(fn (Builder $query, array $data) => match ($data['value'] ?? null) {
                        null, '' => $query,
                        'proses' => $query->whereNull('status_akhir'),
                        default => $query->where('status_akhir', $data['value']),
                    }),
            ])
            ->recordActions([
                Action::make('buka')->label('Buka')->icon(Heroicon::OutlinedArrowTopRightOnSquare)->url(fn (KasusPembinaan $k) => route('guru.pembinaan.detail', $k)),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageKasusPembinaan::route('/')];
    }
}
