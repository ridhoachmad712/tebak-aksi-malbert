<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengumumanResource\Pages\ManagePengumuman;
use App\Models\Pengumuman;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class PengumumanResource extends Resource
{
    protected static ?string $model = Pengumuman::class;

    protected static ?string $slug = 'pengumuman';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|UnitEnum|null $navigationGroup = 'Halaman Publik';

    protected static ?string $modelLabel = 'pengumuman';

    protected static ?string $pluralModelLabel = 'Pengumuman';

    protected static ?int $navigationSort = -1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Textarea::make('isi')->label('Isi pengumuman')->required()->rows(2)->maxLength(300)->columnSpanFull()
                ->placeholder('Contoh: Kegiatan Teras Baca libur pada 25 Oktober karena kegiatan sekolah.'),
            TextInput::make('tautan')->label('Tautan "Selengkapnya"')->url()->maxLength(255)->columnSpanFull()
                ->helperText('Opsional, misalnya alamat berita di situs ini.'),
            Select::make('sasaran')->label('Tampilkan di')->options(Pengumuman::SASARAN)->default('semua')->required(),
            Toggle::make('aktif')->default(true)->inline(false),
            DatePicker::make('mulai')->label('Mulai tampil')->required()->default(today())->native(false)->displayFormat('d/m/Y'),
            DatePicker::make('selesai')->label('Berhenti tampil setelah')->native(false)->displayFormat('d/m/Y')->afterOrEqual('mulai')
                ->helperText('Kosongkan bila tampil sampai dinonaktifkan.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->description('Pengumuman yang sedang berlaku tampil sebagai pita di bagian atas halaman.')
            ->defaultSort('mulai', 'desc')
            ->columns([
                TextColumn::make('isi')->wrap()->weight('bold')->searchable(),
                TextColumn::make('sasaran')->formatStateUsing(fn ($state) => Pengumuman::SASARAN[$state] ?? $state),
                TextColumn::make('mulai')->label('Periode')->date('d/m/Y')
                    ->description(fn (Pengumuman $p) => $p->selesai ? 's.d. '.$p->selesai->format('d/m/Y') : 'tanpa batas'),
                TextColumn::make('status')->badge()
                    ->state(fn (Pengumuman $p) => $p->sedangBerlaku() ? 'Sedang tampil' : ($p->aktif && $p->mulai->isFuture() ? 'Terjadwal' : 'Tidak tampil'))
                    ->color(fn ($state) => match ($state) { 'Sedang tampil' => 'success', 'Terjadwal' => 'info', default => 'gray' }),
                ToggleColumn::make('aktif'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManagePengumuman::route('/')];
    }
}
