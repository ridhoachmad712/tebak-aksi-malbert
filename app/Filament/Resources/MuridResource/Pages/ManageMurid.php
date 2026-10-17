<?php

namespace App\Filament\Resources\MuridResource\Pages;

use App\Filament\Resources\MuridResource;
use App\Models\Murid;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

class ManageMurid extends ManageRecords
{
    protected static string $resource = MuridResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('impor')->label('Impor dari Excel')->icon(Heroicon::OutlinedArrowUpTray)->color('gray')
                ->modalDescription('Baris pertama berisi judul kolom: Nama, NIS, NISN, JK (L/P). Hanya kolom Nama yang wajib. Format .xlsx atau .csv.')
                ->schema([
                    Select::make('kelas_id')->label('Masukkan ke kelas')->options(fn () => MuridResource::opsiKelasAktif())->required(),
                    FileUpload::make('berkas')->label('Berkas Excel/CSV')->disk('local')->directory('impor')->required()
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv', 'text/plain', 'application/vnd.ms-excel']),
                ])
                ->action(function (array $data) {
                    $path = Storage::disk('local')->path($data['berkas']);
                    $jumlah = $this->impor($path, (int) $data['kelas_id']);
                    Storage::disk('local')->delete($data['berkas']);
                    Notification::make()->success()->title("$jumlah murid berhasil diimpor")->send();
                }),
            CreateAction::make()->label('Tambah murid')->using(function (array $data) {
                $kelasId = $data['kelas_id'] ?? null;
                unset($data['kelas_id']);
                $murid = Murid::create($data);
                MuridResource::masukkanKeKelas($murid, $kelasId);

                return $murid;
            }),
        ];
    }

    protected function impor(string $path, int $kelasId): int
    {
        $reader = str_ends_with(strtolower($path), '.csv') ? new CsvReader : new XlsxReader;
        $reader->open($path);
        $jumlah = 0;
        $kolom = null;

        DB::transaction(function () use ($reader, $kelasId, &$jumlah, &$kolom) {
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
                    $murid = Murid::create([
                        'nama' => $nama,
                        'nis' => ($baris['nis'] ?? '') ?: null,
                        'nisn' => ($baris['nisn'] ?? '') ?: null,
                        'jenis_kelamin' => in_array($jk, ['L', 'P'], true) ? $jk : null,
                    ]);
                    MuridResource::masukkanKeKelas($murid, $kelasId);
                    $jumlah++;
                }
                break;
            }
        });
        $reader->close();

        return $jumlah;
    }
}
