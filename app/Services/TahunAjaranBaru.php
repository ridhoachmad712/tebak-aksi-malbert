<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\DB;

/**
 * Menyiapkan tahun ajaran baru dari tahun ajaran sebelumnya: menyalin kelas, menaikkan murid
 * ke tingkat berikutnya (4A → 5A), dan menonaktifkan murid kelas VI yang lulus.
 * Riwayat kelas lama tetap tersimpan karena keanggotaan murid dicatat per tahun ajaran.
 */
class TahunAjaranBaru
{
    /**
     * @param  array{nama: string, mulai: string, selesai: string, salin_kelas: bool, salin_guru: bool, naikkan_murid: bool, luluskan_kelas_6: bool, aktifkan: bool}  $data
     * @return array{tahun_ajaran: TahunAjaran, kelas: int, dinaikkan: int, lulus: int}
     */
    public static function jalankan(TahunAjaran $lama, array $data): array
    {
        return DB::transaction(function () use ($lama, $data) {
            $baru = TahunAjaran::create([
                'nama' => $data['nama'],
                'mulai' => $data['mulai'],
                'selesai' => $data['selesai'],
                'aktif' => false,
            ]);

            $hasil = ['tahun_ajaran' => $baru, 'kelas' => 0, 'dinaikkan' => 0, 'lulus' => 0];
            $kelasLama = $lama->kelas()->with(['guruMapel', 'muridAktif'])->orderBy('tingkat')->orderBy('nama')->get();

            if ($data['salin_kelas']) {
                foreach ($kelasLama as $k) {
                    $kelas = Kelas::create([
                        'tahun_ajaran_id' => $baru->id,
                        'tingkat' => $k->tingkat,
                        'nama' => $k->nama,
                        'wali_kelas_id' => $data['salin_guru'] ? $k->wali_kelas_id : null,
                    ]);
                    if ($data['salin_guru']) {
                        $kelas->guruMapel()->sync($k->guruMapel->pluck('id'));
                    }
                    $hasil['kelas']++;
                }
            }

            if ($data['naikkan_murid']) {
                $kelasBaru = $baru->kelas()->get()->keyBy('nama');
                foreach ($kelasLama as $k) {
                    if ($k->tingkat >= 6) {
                        if ($data['luluskan_kelas_6']) {
                            $hasil['lulus'] += $k->muridAktif->count();
                            $k->muridAktif->each->update(['aktif' => false]);
                        }

                        continue;
                    }

                    // 4A naik ke 5A: tingkat bertambah, akhiran nama kelas tetap
                    $akhiran = preg_replace('/^\d+/', '', $k->nama);
                    $tujuan = $kelasBaru->get(($k->tingkat + 1).$akhiran);
                    if ($tujuan) {
                        $tujuan->murid()->syncWithoutDetaching($k->muridAktif->pluck('id'));
                        $hasil['dinaikkan'] += $k->muridAktif->count();
                    }
                }
            }

            if ($data['aktifkan']) {
                $baru->update(['aktif' => true]);
            }

            return $hasil;
        });
    }
}
