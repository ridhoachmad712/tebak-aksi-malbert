<?php

namespace Database\Seeders;

use App\Models\AspekKarakter;
use App\Models\JenisKarya;
use App\Models\JenisKegiatan;
use App\Models\JenisPelanggaran;
use App\Models\TindakanPembinaan;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

/** Data awal yang dibutuhkan aplikasi: akun admin dan daftar baku (dapat diubah admin). */
class DataAwalSeeder extends Seeder
{
    public function run(): void
    {
        $sandi = env('ADMIN_PASSWORD');
        if (app()->isProduction() && (blank($sandi) || strlen($sandi) < 10 || in_array($sandi, ['password', 'ganti-dengan-kata-sandi-kuat'], true))) {
            throw new RuntimeException('Isi ADMIN_PASSWORD di .env dengan kata sandi kuat (minimal 10 karakter) sebelum menjalankan seeder di server.');
        }

        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@tebakaksi.test')],
            [
                'name' => 'Admin TEBAK AKSI',
                'password' => $sandi ?: 'password',
                'peran' => 'admin',
                'status' => 'aktif',
            ],
        );

        $aspek = [];
        foreach (['Disiplin', 'Tanggung Jawab', 'Kepedulian', 'Kerja Sama', 'Percaya Diri', 'Kesantunan'] as $i => $nama) {
            $aspek[$nama] = AspekKarakter::firstOrCreate(['nama' => $nama], ['urutan' => $i + 1])->id;
        }

        $pelanggaran = [
            'Datang terlambat' => 'Disiplin',
            'Tidak mengikuti kegiatan tanpa alasan' => 'Disiplin',
            'Tidak mengerjakan tugas' => 'Tanggung Jawab',
            'Mengganggu kegiatan pembelajaran' => 'Disiplin',
            'Tidak menjaga kebersihan' => 'Kepedulian',
            'Tidak menjaga fasilitas/buku' => 'Tanggung Jawab',
            'Berkata kurang santun' => 'Kesantunan',
            'Mengganggu atau mengejek teman' => 'Kepedulian',
            'Tidak mematuhi aturan kelas/Teras Baca' => 'Disiplin',
        ];
        $i = 0;
        foreach ($pelanggaran as $nama => $namaAspek) {
            JenisPelanggaran::firstOrCreate(['nama' => $nama], ['aspek_karakter_id' => $aspek[$namaAspek], 'urutan' => ++$i]);
        }

        foreach (['Teguran lisan', 'Nasihat pribadi', 'Pendampingan membaca', 'Tugas membaca tambahan', 'Pemberitahuan orang tua'] as $i => $nama) {
            TindakanPembinaan::firstOrCreate(['nama' => $nama], ['urutan' => $i + 1]);
        }

        $karya = [
            'rendah' => ['Gambar bercerita', 'Kalimat sederhana', 'Kartu cerita', 'Komik sederhana'],
            'tinggi' => ['Rangkuman', 'Cerita pendek', 'Puisi', 'Poster', 'Komik', 'Laporan sederhana', 'Infografis', 'Karya digital'],
        ];
        $i = 0;
        foreach ($karya as $jenjang => $daftar) {
            foreach ($daftar as $nama) {
                JenisKarya::firstOrCreate(['nama' => $nama], ['jenjang' => $jenjang, 'urutan' => ++$i]);
            }
        }

        foreach (['Membaca 15 menit', 'Diskusi buku', 'Menulis/rangkuman', 'Membuat karya', 'Refleksi karakter', 'Presentasi karya'] as $i => $nama) {
            JenisKegiatan::firstOrCreate(['nama' => $nama], ['urutan' => $i + 1]);
        }
    }
}
