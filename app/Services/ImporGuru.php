<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

/**
 * Impor akun guru dari Excel/CSV. Kolom: Nama, Email (wajib), NIP, No HP, Jenis Guru (Kelas/Mapel), Kelas.
 * Semua akun langsung aktif dengan kata sandi awal yang sama, yang dapat diganti guru sendiri.
 * Email yang sudah terdaftar tidak dibuat ganda, dan wali kelas yang sudah ada tidak ditimpa.
 */
class ImporGuru
{
    /** @return array{baru: int, sudah_ada: list<string>, dilewati: list<string>, catatan: list<string>} */
    public static function dariBerkas(string $path, string $kataSandi): array
    {
        $reader = str_ends_with(strtolower($path), '.csv') ? new CsvReader : new XlsxReader;
        $reader->open($path);
        $baris = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $baris[] = array_map(fn ($v) => trim((string) $v), $row->toArray());
            }
            break; // hanya lembar pertama; lembar Petunjuk tidak dibaca
        }
        $reader->close();

        return self::dariBaris($baris, $kataSandi);
    }

    /** @param list<list<string>> $baris baris pertama berisi judul kolom */
    public static function dariBaris(array $baris, string $kataSandi): array
    {
        $hasil = ['baru' => 0, 'sudah_ada' => [], 'dilewati' => [], 'catatan' => []];
        $judul = array_map(fn ($j) => mb_strtolower(trim($j)), array_shift($baris) ?? []);
        $kelasAktif = Kelas::aktif()->get()->keyBy(fn ($k) => mb_strtolower($k->nama));

        DB::transaction(function () use ($baris, $judul, $kataSandi, $kelasAktif, &$hasil) {
            foreach ($baris as $i => $sel) {
                $nomor = $i + 2; // nomor baris di Excel (baris 1 = judul)
                $data = [];
                foreach ($judul as $j => $nama) {
                    $data[$nama] = $sel[$j] ?? '';
                }
                $nama = $data['nama'] ?? $data['nama lengkap'] ?? '';
                $email = mb_strtolower($data['email'] ?? '');
                if ($nama === '' && $email === '') {
                    continue;
                }
                if ($nama === '') {
                    $hasil['dilewati'][] = "Baris {$nomor}: nama kosong";

                    continue;
                }
                if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $hasil['dilewati'][] = "Baris {$nomor} ({$nama}): email kosong atau tidak valid";

                    continue;
                }
                if (User::where('email', $email)->exists()) {
                    $hasil['sudah_ada'][] = $nama;

                    continue;
                }

                $jenisTeks = mb_strtolower($data['jenis guru'] ?? $data['jenis'] ?? '');
                $jenis = str_contains($jenisTeks, 'mapel') || str_contains($jenisTeks, 'mata') ? 'mapel' : 'kelas';

                $guru = User::create([
                    'name' => $nama,
                    'email' => $email,
                    'password' => $kataSandi,
                    'peran' => 'guru',
                    'jenis_guru' => $jenis,
                    'status' => 'aktif',
                    'nip' => ($data['nip'] ?? '') ?: null,
                    'telepon' => ($data['no hp'] ?? $data['no. hp'] ?? $data['telepon'] ?? '') ?: null,
                ]);
                $hasil['baru']++;

                // Kelas: "4A" untuk guru kelas, "4A, 5A, 6A" untuk guru mata pelajaran
                $daftarKelas = collect(preg_split('/[,;\/]+/', $data['kelas'] ?? ''))
                    ->map(fn ($k) => mb_strtolower(trim(preg_replace('/^kelas\s+/i', '', trim($k)))))
                    ->filter();
                $tidakDikenal = $daftarKelas->reject(fn ($k) => $kelasAktif->has($k));
                if ($tidakDikenal->isNotEmpty()) {
                    $hasil['catatan'][] = "{$nama}: kelas ".$tidakDikenal->map(fn ($k) => strtoupper($k))->join(', ').' tidak ditemukan di tahun ajaran aktif';
                }
                $kelas = $daftarKelas->filter(fn ($k) => $kelasAktif->has($k))->map(fn ($k) => $kelasAktif[$k]);

                if ($jenis === 'mapel') {
                    $guru->kelasMapel()->sync($kelas->pluck('id'));
                } elseif ($k = $kelas->first()) {
                    if ($k->wali_kelas_id) {
                        $hasil['catatan'][] = "{$nama}: Kelas {$k->nama} sudah punya wali kelas, jadi tidak diubah";
                    } else {
                        $k->update(['wali_kelas_id' => $guru->id]);
                    }
                }
            }
        });

        return $hasil;
    }
}
