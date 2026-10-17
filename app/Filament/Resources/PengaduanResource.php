<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengaduanResource\Pages\ManagePengaduan;
use App\Models\Pengaduan;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class PengaduanResource extends Resource
{
    protected static ?string $model = Pengaduan::class;

    protected static ?string $slug = 'pengaduan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'Halaman Publik';

    protected static ?string $modelLabel = 'pengaduan';

    protected static ?string $pluralModelLabel = 'Pengaduan Masuk';

    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $jumlah = Pengaduan::where('status', 'baru')->count();

        return $jumlah ? (string) $jumlah : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Isi pengaduan')->columns(2)->schema([
                TextEntry::make('nama')->placeholder('Tanpa nama'),
                TextEntry::make('kontak')->placeholder('-'),
                TextEntry::make('kategori')->formatStateUsing(fn ($state) => Pengaduan::KATEGORI[$state] ?? $state),
                TextEntry::make('created_at')->label('Dikirim')->dateTime('d/m/Y H.i'),
                TextEntry::make('isi')->columnSpanFull(),
            ]),
            Select::make('status')->options(Pengaduan::STATUS)->required(),
            Textarea::make('tindak_lanjut')->label('Catatan tindak lanjut')->rows(3)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Tanggal')->dateTime('d/m/Y H.i')->sortable(),
                TextColumn::make('kategori')->badge()->formatStateUsing(fn ($state) => Pengaduan::KATEGORI[$state] ?? $state),
                TextColumn::make('nama')->placeholder('Tanpa nama')->description(fn (Pengaduan $p) => $p->kontak),
                TextColumn::make('isi')->limit(80)->wrap()->searchable(),
                TextColumn::make('status')->badge()->formatStateUsing(fn ($state) => Pengaduan::STATUS[$state])
                    ->color(fn ($state) => match ($state) { 'baru' => 'warning', 'diproses' => 'info', default => 'success' }),
            ])
            ->filters([
                SelectFilter::make('status')->options(Pengaduan::STATUS),
                SelectFilter::make('kategori')->options(Pengaduan::KATEGORI),
            ])
            ->recordActions([EditAction::make()->label('Tindak lanjut'), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManagePengaduan::route('/')];
    }
}
