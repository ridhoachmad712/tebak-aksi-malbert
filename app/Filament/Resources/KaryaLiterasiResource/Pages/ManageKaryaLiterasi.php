<?php

namespace App\Filament\Resources\KaryaLiterasiResource\Pages;

use App\Filament\Resources\KaryaLiterasiResource;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Contracts\View\View;

class ManageKaryaLiterasi extends ManageRecords
{
    protected static string $resource = KaryaLiterasiResource::class;

    /** Penampil foto yang bisa diperbesar, dibuka dari foto pada kartu karya. */
    public function getFooter(): ?View
    {
        return view('filament.penampil-foto');
    }

    // Karya dicatat guru di halaman guru, jadi tidak ada tombol tambah di sini
}
