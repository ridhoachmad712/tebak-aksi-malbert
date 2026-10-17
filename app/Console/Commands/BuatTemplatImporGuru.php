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
 * Membuat ulang public/templat/templat-impor-guru.xlsx.
 * Kolom NIP dan No HP diformat sebagai teks agar angka nol di depan tidak hilang di Excel.
 */
#[Signature('templat:impor-guru')]
#[Description('Buat templat Excel untuk impor data guru')]
class BuatTemplatImporGuru extends Command
{
    public function handle(): int
    {
        $tujuan = public_path('templat/templat-impor-guru.xlsx');
        @mkdir(dirname($tujuan), 0775, true);

        $tebal = (new Style)->setFontBold()->setBackgroundColor('FFEDD5');
        $teks = (new Style)->setFormat('@');

        $writer = new Writer;
        $writer->openToFile($tujuan);

        $lembar = $writer->getCurrentSheet();
        $lembar->setName('Guru');
        $lembar->setColumnWidth(34, 1);
        $lembar->setColumnWidth(32, 2);
        $lembar->setColumnWidth(22, 3);
        $lembar->setColumnWidth(16, 4);
        $lembar->setColumnWidth(14, 5);
        $lembar->setColumnWidth(16, 6);
        $writer->addRow(Row::fromValues(['Nama', 'Email', 'NIP', 'No HP', 'Jenis Guru', 'Kelas'], $tebal));

        // Baris kosong siap isi; NIP dan No HP sudah berformat teks
        for ($i = 0; $i < 40; $i++) {
            $writer->addRow(new Row([
                Cell::fromValue(''),
                Cell::fromValue(''),
                Cell::fromValue('', $teks),
                Cell::fromValue('', $teks),
                Cell::fromValue(''),
                Cell::fromValue(''),
            ]));
        }

        $writer->addNewSheetAndMakeItCurrent()->setName('Petunjuk');
        $writer->getCurrentSheet()->setColumnWidth(100, 1);
        foreach ([
            ['PETUNJUK IMPOR DATA GURU TEBAK AKSI', true],
            ['1. Isi data di lembar "Guru". Jangan mengubah, menghapus, atau memindahkan judul kolom di baris pertama.', false],
            ['2. Nama: wajib, nama lengkap beserta gelar (contoh penulisan: Nama Guru, S.Pd.).', false],
            ['3. Email: wajib dan harus berbeda untuk setiap guru. Email inilah yang dipakai guru untuk masuk ke aplikasi.', false],
            ['4. NIP dan No HP: boleh dikosongkan. Kolom ini sudah berformat teks agar angka 0 di depan tidak hilang.', false],
            ['5. Jenis Guru: tulis Kelas untuk guru kelas (wali kelas), atau Mapel untuk guru mata pelajaran. Bila kosong, dianggap guru kelas.', false],
            ['6. Kelas: untuk guru kelas tulis satu kelas, misalnya 3A. Untuk guru mapel boleh beberapa kelas dipisah koma, misalnya 4A, 5A, 6A.', false],
            ['   Nama kelas harus sama dengan yang dibuat admin di menu Kelas pada tahun ajaran aktif. Kelas boleh dikosongkan dan diatur nanti.', false],
            ['7. Kata sandi awal diisi admin saat mengimpor dan berlaku untuk semua guru di berkas ini. Minta guru menggantinya setelah masuk.', false],
            ['8. Guru yang email-nya sudah terdaftar tidak dibuat ganda. Kelas yang sudah punya wali kelas tidak ditimpa.', false],
            ['9. Simpan sebagai .xlsx (Excel) atau .csv, lalu unggah di panel admin: menu Pengguna, tombol Impor guru dari Excel.', false],
            ['Lembar Petunjuk ini tidak ikut dibaca saat impor; yang dibaca hanya lembar pertama ("Guru").', false],
        ] as [$isi, $judul]) {
            $writer->addRow(Row::fromValues([$isi], $judul ? (new Style)->setFontBold() : null));
        }

        $writer->close();
        $this->info("Templat dibuat: {$tujuan}");

        return self::SUCCESS;
    }
}
