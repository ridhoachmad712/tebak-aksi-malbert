<?php

namespace App\Filament\Resources\MuridResource\Pages;

use App\Filament\Resources\MuridResource;
use App\Models\Kelas;
use App\Models\Murid;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Url;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

class ManageMurid extends ManageRecords
{
    protected static string $resource = MuridResource::class;

    /** Kelas yang sedang dibuka: id kelas, 'tanpa' (belum punya kelas), 'semua', atau null (halaman pilih kelas). */
    #[Url]
    public ?string $kelas = null;

    public function kelasTerpilih(): ?Kelas
    {
        return ctype_digit((string) $this->kelas) ? Kelas::find($this->kelas) : null;
    }

    public function getHeading(): string
    {
        return match (true) {
            $this->kelas === null => 'Murid',
            $this->kelas === 'tanpa' => 'Murid yang belum punya kelas',
            $this->kelas === 'semua' => 'Semua murid',
            default => 'Murid Kelas '.($this->kelasTerpilih()?->nama ?? '-'),
        };
    }

    public function getSubheading(): ?string
    {
        return $this->kelas === null ? 'Pilih kelas untuk melihat dan mengelola daftar muridnya.' : null;
    }

    public function content(Schema $schema): Schema
    {
        if ($this->kelas !== null) {
            return parent::content($schema);
        }

        return $schema->components([
            View::make('filament.pilih-kelas-murid')->viewData([
                'kelasList' => Kelas::aktif()->with('waliKelas')
                    ->withCount(['murid as murid_aktif_count' => fn ($q) => $q->where('murid.aktif', true)])
                    ->orderBy('tingkat')->orderBy('nama')->get(),
                'tanpaKelas' => Murid::where('aktif', true)->whereDoesntHave('kelas', fn ($q) => $q->aktif())->count(),
                'semua' => Murid::count(),
            ]),
        ]);
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        return match (true) {
            $this->kelas === 'tanpa' => $query->whereDoesntHave('kelas', fn ($q) => $q->aktif()),
            ctype_digit((string) $this->kelas) => $query->whereHas('kelas', fn ($q) => $q->whereKey($this->kelas)),
            default => $query,
        };
    }

    protected function getHeaderActions(): array
    {
        $kelasId = $this->kelasTerpilih()?->id;

        return [
            Action::make('gantiKelas')->label('Ganti kelas')->icon(Heroicon::OutlinedArrowLeft)->color('gray')
                ->url(MuridResource::getUrl('index'))->visible($this->kelas !== null),
            Action::make('impor')->label('Impor dari Excel')->icon(Heroicon::OutlinedArrowUpTray)->color('gray')
                ->modalDescription(new HtmlString('Gunakan templat agar kolomnya sesuai: <a href="'.asset('templat/templat-impor-murid.xlsx').'" class="font-semibold text-primary-600 underline">unduh templat Excel</a>. Kolom: Nama (wajib), NIS, NISN, JK (L/P). Satu berkas untuk satu kelas. Murid dengan NISN/NIS yang sudah terdaftar tidak akan dibuat ganda.'))
                ->schema([
                    Select::make('kelas_id')->label('Masukkan ke kelas')->options(fn () => MuridResource::opsiKelasAktif())->default($kelasId)->required(),
                    FileUpload::make('berkas')->label('Berkas Excel/CSV')->disk('local')->directory('impor')->required()
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv', 'text/plain', 'application/vnd.ms-excel']),
                ])
                ->action(function (array $data) {
                    $path = Storage::disk('local')->path($data['berkas']);
                    $hasil = $this->impor($path, (int) $data['kelas_id']);
                    Storage::disk('local')->delete($data['berkas']);
                    Notification::make()->success()->title("{$hasil['baru']} murid baru diimpor")
                        ->body($hasil['sudah_ada'] ? "{$hasil['sudah_ada']} murid sudah terdaftar (NISN/NIS sama), jadi tidak dibuat ganda dan hanya dimasukkan ke kelas ini." : null)
                        ->send();
                }),
            CreateAction::make()->label('Tambah murid')->fillForm(['kelas_id' => $kelasId, 'aktif' => true])->using(function (array $data) {
                $kelasId = $data['kelas_id'] ?? null;
                unset($data['kelas_id']);
                $murid = Murid::create($data);
                MuridResource::masukkanKeKelas($murid, $kelasId);

                return $murid;
            }),
        ];
    }

    /** @return array{baru: int, sudah_ada: int} */
    protected function impor(string $path, int $kelasId): array
    {
        $reader = str_ends_with(strtolower($path), '.csv') ? new CsvReader : new XlsxReader;
        $reader->open($path);
        $jumlah = 0;
        $sudahAda = 0;
        $kolom = null;

        DB::transaction(function () use ($reader, $kelasId, &$jumlah, &$sudahAda, &$kolom) {
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $sel = array_map(fn ($v) => trim((string) $v), $row->toArray());
                    if ($kolom === null) {
                        $kolom = array_map('strtolower', $sel);

                        continue;
                    }
                    $baris = array_combine(array_slice($kolom, 0, count($sel)), array_slice($sel, 0, count($kolom)));
                    $nama = $baris['nama'] ?? $baris['nama murid'] ?? '';
                    if ($nama === '') {
                        continue;
                    }
                    $jk = strtoupper(substr($baris['jk'] ?? $baris['jenis kelamin'] ?? '', 0, 1));
                    $nis = ($baris['nis'] ?? '') ?: null;
                    $nisn = ($baris['nisn'] ?? '') ?: null;

                    // Murid yang NISN/NIS-nya sudah terdaftar tidak dibuat ganda; cukup dimasukkan ke kelas ini
                    $murid = match (true) {
                        (bool) $nisn => Murid::where('nisn', $nisn)->first(),
                        (bool) $nis => Murid::where('nis', $nis)->first(),
                        default => null,
                    };
                    if ($murid) {
                        $sudahAda++;
                    } else {
                        $murid = Murid::create([
                            'nama' => $nama,
                            'nis' => $nis,
                            'nisn' => $nisn,
                            'jenis_kelamin' => in_array($jk, ['L', 'P'], true) ? $jk : null,
                        ]);
                        $jumlah++;
                    }
                    MuridResource::masukkanKeKelas($murid, $kelasId);
                }
                break;
            }
        });
        $reader->close();

        return ['baru' => $jumlah, 'sudah_ada' => $sudahAda];
    }
}
