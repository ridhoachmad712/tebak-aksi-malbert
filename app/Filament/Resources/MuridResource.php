<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MuridResource\Pages\ManageMurid;
use App\Filament\Support\HapusAman;
use App\Models\Kelas;
use App\Models\Murid;
use BackedEnum;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use UnitEnum;

class MuridResource extends Resource
{
    protected static ?string $model = Murid::class;

    protected static ?string $slug = 'murid';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|UnitEnum|null $navigationGroup = 'Pengguna & Kelas';

    protected static ?string $modelLabel = 'murid';

    protected static ?string $pluralModelLabel = 'Murid';

    protected static ?int $navigationSort = 4;

    public static function opsiKelasAktif(): array
    {
        return Kelas::aktif()->orderBy('tingkat')->orderBy('nama')->pluck('nama', 'id')->map(fn ($n) => "Kelas $n")->all();
    }

    /** Memasukkan murid ke kelas; keanggotaan lain di tahun ajaran yang sama dilepas (pindah/naik kelas). */
    public static function masukkanKeKelas(Murid $murid, ?int $kelasId): void
    {
        if (! $kelasId) {
            return;
        }
        $kelas = Kelas::findOrFail($kelasId);
        $lain = $murid->kelas()->where('tahun_ajaran_id', $kelas->tahun_ajaran_id)->pluck('kelas.id');
        $murid->kelas()->detach($lain->reject(fn ($id) => $id === $kelas->id));
        $murid->kelas()->syncWithoutDetaching([$kelas->id]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nama')->label('Nama lengkap')->required()->maxLength(255)->columnSpanFull(),
            Select::make('kelas_id')->label('Kelas (tahun ajaran aktif)')->options(fn () => static::opsiKelasAktif())
                ->afterStateHydrated(fn (Select $component, $record) => $component->state($record?->kelasAktif()?->id)),
            Select::make('jenis_kelamin')->label('Jenis kelamin')->options(['L' => 'Laki-laki', 'P' => 'Perempuan']),
            TextInput::make('nis')->label('NIS')->maxLength(30),
            TextInput::make('nisn')->label('NISN')->maxLength(30),
            Toggle::make('aktif')->default(true)->helperText('Murid nonaktif (pindah sekolah/lulus) tidak muncul di daftar centang guru.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('nama')
            ->modifyQueryUsing(fn ($query) => $query->with(['kelas' => fn ($q) => $q->aktif()]))
            ->columns([
                TextColumn::make('nama')->searchable()->sortable()->weight('bold'),
                TextColumn::make('kelas.nama')->label('Kelas')->badge()->placeholder('Belum ada kelas'),
                TextColumn::make('jenis_kelamin')->label('L/P'),
                TextColumn::make('nis')->label('NIS')->searchable()->toggleable(),
                TextColumn::make('nisn')->label('NISN')->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('aktif')->boolean(),
            ])
            // Pemilahan per kelas dilakukan di halaman pilih kelas (ManageMurid)
            ->filters([
                TernaryFilter::make('aktif')->label('Status')->trueLabel('Aktif')->falseLabel('Nonaktif')->placeholder('Semua')->default(true),
            ])
            ->recordActions([
                EditAction::make()->using(function (Murid $record, array $data) {
                    $kelasId = $data['kelas_id'] ?? null;
                    unset($data['kelas_id']);
                    $record->update($data);
                    static::masukkanKeKelas($record, $kelasId);

                    return $record;
                }),
                HapusAman::aksi(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('pindah')->label('Masukkan ke kelas…')->icon(Heroicon::OutlinedArrowRightCircle)
                        ->schema([Select::make('kelas_id')->label('Kelas tujuan')->options(fn () => Kelas::with('tahunAjaran')->orderByDesc('tahun_ajaran_id')->orderBy('tingkat')->orderBy('nama')->get()->mapWithKeys(fn ($k) => [$k->id => "Kelas {$k->nama} ({$k->tahunAjaran->nama})"]))->required()])
                        ->modalDescription('Dipakai untuk pindah kelas atau kenaikan kelas di tahun ajaran baru. Riwayat kelas sebelumnya tetap tersimpan.')
                        ->action(fn (Collection $records, array $data) => $records->each(fn (Murid $m) => static::masukkanKeKelas($m, (int) $data['kelas_id'])))
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Murid dimasukkan ke kelas'),
                    BulkAction::make('nonaktifkan')->label('Nonaktifkan')->icon(Heroicon::OutlinedNoSymbol)->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['aktif' => false]))
                        ->deselectRecordsAfterCompletion(),
                    HapusAman::aksiMassal(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageMurid::route('/')];
    }
}
