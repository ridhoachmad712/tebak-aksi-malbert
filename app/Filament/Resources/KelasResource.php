<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KelasResource\Pages\ManageKelas;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\User;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;
use UnitEnum;

class KelasResource extends Resource
{
    protected static ?string $model = Kelas::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static string|UnitEnum|null $navigationGroup = 'Pengguna & Kelas';

    protected static ?string $modelLabel = 'kelas';

    protected static ?string $pluralModelLabel = 'Kelas';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('tahun_ajaran_id')->label('Tahun ajaran')->relationship('tahunAjaran', 'nama')
                ->default(fn () => TahunAjaran::aktif()?->id)->required(),
            Select::make('tingkat')->options(array_combine(range(1, 6), ['I', 'II', 'III', 'IV', 'V', 'VI']))->required(),
            TextInput::make('nama')->label('Nama kelas')->placeholder('Contoh: 4A')->required()->maxLength(20)
                ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule, $get) => $rule->where('tahun_ajaran_id', $get('tahun_ajaran_id'))),
            Select::make('wali_kelas_id')->label('Wali kelas (guru kelas)')
                ->options(fn () => User::guru()->where('status', 'aktif')->where('jenis_guru', 'kelas')->orderBy('name')->pluck('name', 'id'))
                ->searchable(),
            Select::make('guruMapel')->label('Guru mata pelajaran yang ditugaskan')
                ->relationship('guruMapel', 'name', fn ($query) => $query->where('peran', 'guru')->where('jenis_guru', 'mapel')->orderBy('name'))
                ->multiple()->preload()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort(fn ($query) => $query->orderBy('tingkat')->orderBy('nama'))
            ->modifyQueryUsing(fn ($query) => $query->with(['waliKelas', 'guruMapel', 'tahunAjaran'])->withCount('murid'))
            ->columns([
                TextColumn::make('nama')->label('Kelas')->weight('bold')->searchable()->prefix('Kelas '),
                TextColumn::make('tahunAjaran.nama')->label('Tahun ajaran'),
                TextColumn::make('waliKelas.name')->label('Wali kelas')->placeholder('Belum ada'),
                TextColumn::make('guruMapel.name')->label('Guru mapel')->badge()->color('gray')->placeholder('-'),
                TextColumn::make('murid_count')->label('Jumlah murid'),
            ])
            ->filters([
                SelectFilter::make('tahun_ajaran_id')->label('Tahun ajaran')->relationship('tahunAjaran', 'nama')
                    ->default(fn () => TahunAjaran::aktif()?->id),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageKelas::route('/')];
    }
}
