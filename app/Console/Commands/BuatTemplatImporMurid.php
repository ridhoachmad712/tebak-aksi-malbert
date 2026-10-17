<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;

/**
 * Membuat ulang public/templat/templat-impor-murid.xlsx.
 * Kolom NIS dan NISN diformat sebagai teks agar angka nol di depan tidak hilang di Excel.
 */
#[Signature('templat:impor-murid')]
#[Description('Buat templat Excel untuk impor data murid')]
class BuatTemplatImporMurid extends Command
{
    public function handle(): int
    {
        $tujuan = public_path('templat/templat-impor-murid.xlsx');
        @mkdir(dirname($tujuan), 0775, true);

        $tebal = (new Style)->setFontBold()->setBackgroundColor('FFEDD5');
        $teks = (new Style)->setFormat('@');

        $writer = new Writer;
        $writer->openToFile($tujuan);

        $lembar = $writer->getCurrentSheet();
        $lembar->setName('Murid');
        $lembar->setColumnWidth(36, 1);
        $lembar->setColumnWidth(14, 2, 3);
        $lembar->setColumnWidth(8, 4);
        $writer->addRow(Row::fromValues(['Nama', 'NIS', 'NISN', 'JK'], $tebal));

        // Baris kosong siap isi; NIS dan NISN sudah berformat teks
        for ($i = 0; $i < 60; $i++) {
            $writer->addRow(new Row([
                Cell::fromValue(''),
                Cell::fromValue('', $teks),
                Cell::fromValue('', $teks),
                Cell::fromValue(''),
            ]));
        }

        $writer->addNewSheetAndMakeItCurrent()->setName('Petunjuk');
        $writer->getCurrentSheet()->setColumnWidth(95, 1);
        foreach ([
            ['PETUNJUK IMPOR DATA MURID TEBAK AKSI', true],
            ['1. Isi data di lembar "Murid". Jangan mengubah, menghapus, atau memindahkan judul kolom di baris pertama.', false],
            ['2. Nama: wajib diisi, nama lengkap murid. Baris tanpa nama akan dilewati.', false],
            ['3. NIS dan NISN: boleh dikosongkan. Kolom ini sudah berformat teks agar angka 0 di depan tidak hilang.', false],
            ['4. JK: isi L untuk laki-laki atau P untuk perempuan (boleh juga ditulis Laki-laki/Perempuan).', false],
            ['5. Gunakan satu berkas untuk satu kelas. Kelas tujuan dipilih saat mengimpor di panel admin.', false],
            ['6. Murid yang NISN atau NIS-nya sudah terdaftar tidak akan dibuat ganda; murid itu hanya dimasukkan ke kelas yang dipilih.', false],
            ['7. Simpan sebagai .xlsx (Excel) atau .csv, lalu unggah di panel admin: menu Murid, tombol Impor dari Excel.', false],
            ['Lembar Petunjuk ini tidak ikut dibaca saat impor; yang dibaca hanya lembar pertama ("Murid").', false],
        ] as [$isi, $judul]) {
            $writer->addRow(Row::fromValues([$isi], $judul ? (new Style)->setFontBold() : null));
        }

        $writer->close();
        $this->info("Templat dibuat: {$tujuan}");

        return self::SUCCESS;
    }
}
