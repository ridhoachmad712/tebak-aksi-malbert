<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GaleriResource\Pages\ManageGaleri;
use App\Models\Diseminasi;
use App\Models\Foto;
use App\Models\KaryaLiterasi;
use App\Models\KegiatanHarian;
use BackedEnum;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use UnitEnum;

class GaleriResource extends Resource
{
    protected static ?string $model = Foto::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Halaman Publik';

    protected static ?string $modelLabel = 'foto';

    protected static ?string $pluralModelLabel = 'Galeri Dokumentasi';

    protected static ?string $slug = 'galeri';

    protected static ?int $navigationSort = 3;

    public const SUMBER = [
        KegiatanHarian::class => 'Kegiatan Harian',
        KaryaLiterasi::class => 'Karya Literasi',
        Diseminasi::class => 'Diseminasi',
        'langsung' => 'Unggahan Galeri',
    ];

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('keterangan')->maxLength(255)->columnSpanFull()->helperText('Hindari menuliskan nama lengkap murid.'),
            Toggle::make('tampil_galeri')->label('Tampilkan di Galeri publik'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->description('Pilih foto yang tampil di halaman publik. Pastikan foto pantas ditampilkan dan tidak menampilkan data pribadi murid.')
            ->defaultSort('created_at', 'desc')
            ->contentGrid(['md' => 3, 'xl' => 4])
            ->columns([
                ImageColumn::make('path')->disk('public')->label('')->imageHeight(180)->extraImgAttributes(['class' => 'rounded-xl object-cover w-full']),
                TextColumn::make('sumber')->state(fn (Foto $f) => $f->sumber())->badge()->color('gray'),
                TextColumn::make('keterangan')->placeholder('Tanpa keterangan')->wrap(),
                TextColumn::make('created_at')->label('Diunggah')->since()->description(fn (Foto $f) => $f->user?->name),
                ToggleColumn::make('tampil_galeri')->label('Tampil di galeri publik'),
            ])
            ->filters([
                TernaryFilter::make('tampil_galeri')->label('Tampil di galeri'),
                SelectFilter::make('sumber')->options(self::SUMBER)
                    ->query(fn (Builder $query, array $data) => match ($data['value'] ?? null) {
                        null, '' => $query,
                        'langsung' => $query->whereNull('fotoable_type'),
                        default => $query->where('fotoable_type', $data['value']),
                    }),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('tampilkan')->label('Tampilkan di galeri')->icon(Heroicon::OutlinedEye)
                        ->action(fn (Collection $records) => $records->each->update(['tampil_galeri' => true]))->deselectRecordsAfterCompletion(),
                    BulkAction::make('sembunyikan')->label('Sembunyikan dari galeri')->icon(Heroicon::OutlinedEyeSlash)
                        ->action(fn (Collection $records) => $records->each->update(['tampil_galeri' => false]))->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make()->action(fn (Collection $records) => $records->each->delete()),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageGaleri::route('/')];
    }
}
