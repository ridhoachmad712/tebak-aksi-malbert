<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenggunaResource\Pages\ManagePengguna;
use App\Models\Kelas;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rules\Password;
use UnitEnum;

class PenggunaResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'Pengguna & Kelas';

    protected static ?string $modelLabel = 'pengguna';

    protected static ?string $pluralModelLabel = 'Guru & Pengguna';

    protected static ?string $slug = 'pengguna';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $jumlah = User::where('status', 'menunggu')->count();

        return $jumlah ? (string) $jumlah : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Menunggu persetujuan';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Data diri')->columns(2)->schema([
                TextInput::make('name')->label('Nama lengkap')->required()->maxLength(255),
                TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
                TextInput::make('telepon')->label('No. HP/WA')->maxLength(30),
                TextInput::make('nip')->label('NIP')->maxLength(30),
                TextInput::make('keterangan_daftar')->label('Keterangan saat mendaftar')->disabled()->columnSpanFull()
                    ->visible(fn ($record) => filled($record?->keterangan_daftar)),
            ]),
            Section::make('Peran & akses')->columns(2)->schema([
                Select::make('peran')->options(User::PERAN)->default('guru')->required()->live(),
                Select::make('status')->options(User::STATUS)->default('aktif')->required(),
                Select::make('jenis_guru')->label('Jenis guru')->options(User::JENIS_GURU)->default('kelas')->live()
                    ->visible(fn (Get $get) => $get('peran') === 'guru')->required(fn (Get $get) => $get('peran') === 'guru'),
                Select::make('kelas_wali_id')->label('Kelas yang diampu (wali kelas)')
                    ->options(fn () => Kelas::aktif()->orderBy('tingkat')->orderBy('nama')->pluck('nama', 'id')->map(fn ($n) => "Kelas $n"))
                    ->helperText('Guru kelas memegang 1 kelas.')
                    ->afterStateHydrated(fn (Select $component, $record) => $component->state($record?->kelasWali()->aktif()->value('id')))
                    ->visible(fn (Get $get) => $get('peran') === 'guru' && $get('jenis_guru') === 'kelas'),
                Select::make('kelasMapel')->label('Kelas yang ditugaskan')
                    ->relationship('kelasMapel', 'nama', fn ($query) => $query->aktif()->orderBy('tingkat')->orderBy('nama'))
                    ->getOptionLabelFromRecordUsing(fn (Kelas $k) => "Kelas {$k->nama}")
                    ->multiple()->preload()
                    ->helperText('Guru mata pelajaran dapat mencatat di beberapa kelas.')
                    ->visible(fn (Get $get) => $get('peran') === 'guru' && $get('jenis_guru') === 'mapel'),
            ]),
            Section::make('Kata sandi')->schema([
                TextInput::make('password')->label(fn ($record) => $record ? 'Atur ulang kata sandi' : 'Kata sandi')
                    ->password()->revealable()
                    ->rule(Password::min(8))
                    ->required(fn ($record) => ! $record)
                    ->dehydrated(fn ($state) => filled($state))
                    ->helperText(fn ($record) => $record ? 'Kosongkan jika tidak ingin mengubah. Isi untuk mengatur ulang kata sandi guru yang lupa.' : 'Minimal 8 karakter. Sampaikan kepada guru secara langsung.'),
            ]),
        ]);
    }

    /** Menyimpan kelas wali (guru kelas memegang satu kelas di tahun ajaran aktif). */
    public static function simpanKelasWali(User $user, ?int $kelasId): void
    {
        Kelas::aktif()->where('wali_kelas_id', $user->id)->when($kelasId, fn ($q) => $q->whereKeyNot($kelasId))->update(['wali_kelas_id' => null]);

        if ($kelasId && $user->peran === 'guru' && $user->jenis_guru === 'kelas') {
            Kelas::whereKey($kelasId)->update(['wali_kelas_id' => $user->id]);
        }
        if ($user->jenis_guru !== 'mapel' || $user->peran !== 'guru') {
            $user->kelasMapel()->detach();
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->modifyQueryUsing(fn ($query) => $query->with(['kelasWali' => fn ($q) => $q->aktif(), 'kelasMapel' => fn ($q) => $q->aktif()]))
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable()->sortable()->weight('bold')
                    ->description(fn (User $u) => $u->email),
                TextColumn::make('peran')->badge()->formatStateUsing(fn ($state, User $u) => $u->isGuru() ? (User::JENIS_GURU[$u->jenis_guru] ?? 'Guru') : User::PERAN[$state])
                    ->color(fn ($state) => match ($state) { 'admin' => 'danger', 'kepsek' => 'info', default => 'gray' }),
                TextColumn::make('kelas')->label('Kelas')->state(fn (User $u) => $u->kelasWali->pluck('nama')->merge($u->kelasMapel->pluck('nama'))->unique()->join(', ') ?: '-'),
                TextColumn::make('status')->badge()->formatStateUsing(fn ($state) => User::STATUS[$state])
                    ->color(fn ($state) => match ($state) { 'aktif' => 'success', 'menunggu' => 'warning', default => 'gray' }),
                TextColumn::make('keterangan_daftar')->label('Keterangan daftar')->toggleable(isToggledHiddenByDefault: true)->wrap(),
            ])
            ->filters([
                SelectFilter::make('status')->options(User::STATUS),
                SelectFilter::make('peran')->options(User::PERAN),
            ])
            ->recordActions([
                Action::make('setujui')->label('Setujui')->icon(Heroicon::OutlinedCheckBadge)->color('success')
                    ->visible(fn (User $u) => $u->status === 'menunggu')
                    ->modalHeading(fn (User $u) => "Setujui {$u->name}")
                    ->modalDescription(fn (User $u) => 'Keterangan saat mendaftar: '.($u->keterangan_daftar ?: '-'))
                    ->schema([
                        Select::make('jenis_guru')->label('Jenis guru')->options(User::JENIS_GURU)->required()->live()
                            ->default(fn (User $u) => $u->jenis_guru ?? 'kelas'),
                        Select::make('kelas_wali_id')->label('Kelas yang diampu')
                            ->options(fn () => Kelas::aktif()->orderBy('tingkat')->orderBy('nama')->pluck('nama', 'id')->map(fn ($n) => "Kelas $n"))
                            ->visible(fn (Get $get) => $get('jenis_guru') === 'kelas'),
                        Select::make('kelas_mapel')->label('Kelas yang ditugaskan')->multiple()
                            ->options(fn () => Kelas::aktif()->orderBy('tingkat')->orderBy('nama')->pluck('nama', 'id')->map(fn ($n) => "Kelas $n"))
                            ->visible(fn (Get $get) => $get('jenis_guru') === 'mapel'),
                    ])
                    ->action(function (User $u, array $data) {
                        $u->update(['status' => 'aktif', 'jenis_guru' => $data['jenis_guru']]);
                        static::simpanKelasWali($u, $data['kelas_wali_id'] ?? null);
                        if ($data['jenis_guru'] === 'mapel') {
                            $u->kelasMapel()->sync($data['kelas_mapel'] ?? []);
                        }
                    })
                    ->successNotificationTitle('Akun guru disetujui'),
                Action::make('tolak')->label('Tolak')->icon(Heroicon::OutlinedXCircle)->color('danger')
                    ->visible(fn (User $u) => $u->status === 'menunggu')
                    ->requiresConfirmation()->modalDescription('Akun akan dinonaktifkan dan tidak bisa dipakai masuk.')
                    ->action(fn (User $u) => $u->update(['status' => 'nonaktif'])),
                EditAction::make()->using(function (User $record, array $data) {
                    $wali = Arr::pull($data, 'kelas_wali_id');
                    $record->update($data);
                    static::simpanKelasWali($record, $wali);

                    return $record;
                }),
                DeleteAction::make()->hidden(fn (User $u) => $u->id === auth()->id()),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManagePengguna::route('/')];
    }
}
