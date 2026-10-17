<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

/**
 * Cadangan yang bisa diunduh admin tanpa akses server:
 * - data: berkas .sql (struktur + isi tabel) yang bisa dipulihkan lewat phpMyAdmin
 * - berkas: arsip .zip berisi foto dan berkas unggahan (storage/app/public)
 * Dibuat dengan PHP murni karena hosting bersama sering tidak mengizinkan mysqldump.
 */
class CadanganData
{
    /** Tabel sementara yang hanya disalin strukturnya, tanpa isi. */
    protected const TANPA_ISI = ['sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs'];

    protected const BARIS_PER_INSERT = 200;

    /** Menulis seluruh cadangan SQL lewat fungsi $tulis agar bisa dialirkan tanpa memakan memori. */
    public static function tulisSql(callable $tulis): void
    {
        $db = DB::connection();
        $driver = $db->getDriverName();
        $mysql = in_array($driver, ['mysql', 'mariadb'], true);
        $pdo = $db->getPdo();

        $tulis('-- Cadangan data TEBAK AKSI'."\n");
        $tulis('-- Dibuat: '.now()->format('Y-m-d H:i:s').' ('.config('app.timezone').'), database: '.$driver."\n");
        $tulis('-- Pulihkan dengan phpMyAdmin: pilih database kosong, buka tab Import, lalu pilih berkas ini.'."\n\n");
        if ($mysql) {
            $tulis("SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");
        }

        foreach (self::daftarTabel($driver) as $tabel => $buat) {
            $q = $mysql ? "`{$tabel}`" : "\"{$tabel}\"";
            $tulis("-- Tabel {$tabel}\nDROP TABLE IF EXISTS {$q};\n{$buat};\n\n");

            if (in_array($tabel, self::TANPA_ISI, true)) {
                continue;
            }

            $kolom = null;
            $nilai = [];
            foreach (DB::table($tabel)->cursor() as $baris) {
                $baris = (array) $baris;
                $kolom ??= implode(', ', array_map(fn ($k) => $mysql ? "`{$k}`" : "\"{$k}\"", array_keys($baris)));
                $nilai[] = '('.implode(', ', array_map(fn ($v) => $v === null ? 'NULL' : (is_bool($v) ? (int) $v : $pdo->quote((string) $v)), $baris)).')';

                if (count($nilai) >= self::BARIS_PER_INSERT) {
                    $tulis("INSERT INTO {$q} ({$kolom}) VALUES\n".implode(",\n", $nilai).";\n");
                    $nilai = [];
                }
            }
            if ($nilai) {
                $tulis("INSERT INTO {$q} ({$kolom}) VALUES\n".implode(",\n", $nilai).";\n");
            }
            $tulis("\n");
        }

        if ($mysql) {
            $tulis("SET FOREIGN_KEY_CHECKS=1;\n");
        }
    }

    /** @return array<string, string> nama tabel => perintah CREATE TABLE */
    protected static function daftarTabel(string $driver): array
    {
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $hasil = [];
            foreach (DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"') as $baris) {
                $tabel = array_values((array) $baris)[0];
                $hasil[$tabel] = (array) DB::selectOne("SHOW CREATE TABLE `{$tabel}`");
                $hasil[$tabel] = $hasil[$tabel]['Create Table'];
            }

            return $hasil;
        }

        if ($driver === 'sqlite') {
            return collect(DB::select("SELECT name, sql FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name"))
                ->mapWithKeys(fn ($t) => [$t->name => $t->sql])->all();
        }

        throw new RuntimeException("Jenis database {$driver} belum didukung untuk cadangan.");
    }

    /** Membuat arsip zip berisi seluruh berkas unggahan; mengembalikan lokasi berkas sementara. */
    public static function buatZipBerkas(): string
    {
        $sumber = Storage::disk('public')->path('');
        $tujuan = storage_path('app/cadangan-berkas-'.now()->format('Ymd-His').'.zip');

        $zip = new ZipArchive;
        if ($zip->open($tujuan, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Arsip cadangan tidak dapat dibuat.');
        }
        foreach (Storage::disk('public')->allFiles() as $berkas) {
            if (! str_starts_with(basename($berkas), '.')) {
                $zip->addFile($sumber.$berkas, $berkas);
            }
        }
        if ($zip->numFiles === 0) {
            $zip->addFromString('BACA-SAYA.txt', 'Belum ada foto atau berkas unggahan saat cadangan dibuat.');
        }
        $zip->close();

        return $tujuan;
    }
}
