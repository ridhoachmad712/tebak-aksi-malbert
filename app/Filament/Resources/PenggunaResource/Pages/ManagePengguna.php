<?php

namespace App\Filament\Resources\PenggunaResource\Pages;

use App\Filament\Resources\PenggunaResource;
use App\Models\User;
use App\Services\ImporGuru;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Password;

class ManagePengguna extends ManageRecords
{
    protected static string $resource = PenggunaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('imporGuru')->label('Impor guru dari Excel')->icon(Heroicon::OutlinedArrowUpTray)->color('gray')
                ->modalHeading('Impor data guru')
                ->modalDescription(new HtmlString('Gunakan templat agar kolomnya sesuai: <a href="'.asset('templat/templat-impor-guru.xlsx').'" class="font-semibold text-primary-600 underline">unduh templat Excel</a>. Kolom: Nama dan Email (wajib), NIP, No HP, Jenis Guru (Kelas/Mapel), Kelas. Akun langsung aktif. Guru dengan email yang sudah terdaftar tidak dibuat ganda.'))
                ->schema([
                    FileUpload::make('berkas')->label('Berkas Excel/CSV')->disk('local')->directory('impor')->required()
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv', 'text/plain', 'application/vnd.ms-excel']),
                    TextInput::make('kata_sandi')->label('Kata sandi awal untuk semua guru yang diimpor')
                        ->password()->revealable()->required()->rule(Password::min(8))
                        ->helperText('Minimal 8 karakter. Sampaikan kepada guru secara langsung, lalu minta guru menggantinya lewat menu Ubah kata sandi.'),
                ])
                ->action(function (array $data) {
                    $hasil = ImporGuru::dariBerkas(Storage::disk('local')->path($data['berkas']), $data['kata_sandi']);
                    Storage::disk('local')->delete($data['berkas']);

                    $rincian = array_filter([
                        $hasil['sudah_ada'] ? count($hasil['sudah_ada']).' guru sudah terdaftar (email sama), tidak dibuat ganda: '.implode(', ', array_slice($hasil['sudah_ada'], 0, 5)).(count($hasil['sudah_ada']) > 5 ? ', dan lainnya' : '').'.' : null,
                        ...array_slice($hasil['dilewati'], 0, 5),
                        ...array_slice($hasil['catatan'], 0, 5),
                    ]);
                    Notification::make()
                        ->title("{$hasil['baru']} guru baru diimpor")
                        ->body($rincian ? new HtmlString(implode('<br>', array_map('e', $rincian))) : null)
                        ->status($hasil['dilewati'] || $hasil['catatan'] ? 'warning' : 'success')
                        ->persistent((bool) $rincian)
                        ->send();
                }),
            CreateAction::make()->label('Tambah pengguna')->using(function (array $data) {
                $wali = Arr::pull($data, 'kelas_wali_id');
                $user = User::create($data);
                PenggunaResource::simpanKelasWali($user, $wali);

                return $user;
            }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua'),
            'menunggu' => Tab::make('Menunggu persetujuan')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'menunggu'))
                ->badge(User::where('status', 'menunggu')->count() ?: null)->badgeColor('warning'),
            'guru' => Tab::make('Guru')->modifyQueryUsing(fn ($query) => $query->where('peran', 'guru')),
        ];
    }
}
