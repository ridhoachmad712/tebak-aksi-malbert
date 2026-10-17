<?php

namespace Database\Seeders;

use App\Models\BaselinePelanggaran;
use App\Models\HariLibur;
use App\Models\JenisKarya;
use App\Models\JenisKegiatan;
use App\Models\JenisPelanggaran;
use App\Models\KaryaLiterasi;
use App\Models\KasusPembinaan;
use App\Models\KegiatanHarian;
use App\Models\Kelas;
use App\Models\Konten;
use App\Models\Murid;
use App\Models\Pengaduan;
use App\Models\Pengaturan;
use App\Models\TahunAjaran;
use App\Models\TindakanPembinaan;
use App\Models\User;
use App\Services\Rekap;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Data contoh fiktif untuk uji coba. Semua nama dibuat acak, bukan data murid asli.
 * Akun contoh memakai kata sandi "password".
 */
class DataContohSeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake('id_ID');
        mt_srand(2026);
        $faker->seed(2026);

        Pengaturan::simpan(['nama_kepala_sekolah' => 'Kepala Sekolah (Contoh)']);

        $ta = TahunAjaran::firstOrCreate(['nama' => '2026/2027'], ['mulai' => '2026-07-13', 'selesai' => '2027-06-30', 'aktif' => true]);
        BaselinePelanggaran::firstOrCreate(['tahun_ajaran_id' => $ta->id, 'semester' => 1], ['jumlah' => 60]);

        foreach (['2026-08-17' => 'Hari Kemerdekaan RI', '2026-09-25' => 'Libur kegiatan sekolah'] as $tgl => $ket) {
            HariLibur::firstOrCreate(['tanggal' => $tgl], ['keterangan' => $ket]);
        }

        User::firstOrCreate(['email' => 'kepsek@tebakaksi.test'], [
            'name' => 'Kepala Sekolah Contoh', 'password' => 'password', 'peran' => 'kepsek', 'status' => 'aktif',
        ]);

        // Guru kelas I–VI
        $kelasList = collect();
        foreach (range(1, 6) as $tingkat) {
            $guru = User::firstOrCreate(['email' => "guru{$tingkat}@tebakaksi.test"], [
                'name' => $faker->firstName().' '.$faker->lastName().', S.Pd.',
                'password' => 'password', 'peran' => 'guru', 'jenis_guru' => 'kelas', 'status' => 'aktif',
            ]);
            $kelasList->push(Kelas::firstOrCreate(
                ['tahun_ajaran_id' => $ta->id, 'nama' => "{$tingkat}A"],
                ['tingkat' => $tingkat, 'wali_kelas_id' => $guru->id],
            ));
        }

        // Guru mapel dengan beberapa kelas
        $mapel = User::firstOrCreate(['email' => 'pjok@tebakaksi.test'], [
            'name' => $faker->firstName().' '.$faker->lastName().', S.Pd. (PJOK)',
            'password' => 'password', 'peran' => 'guru', 'jenis_guru' => 'mapel', 'status' => 'aktif',
        ]);
        $mapel->kelasMapel()->syncWithoutDetaching($kelasList->whereIn('tingkat', [4, 5, 6])->pluck('id'));

        // Pendaftar yang menunggu persetujuan
        User::firstOrCreate(['email' => 'pendaftar@tebakaksi.test'], [
            'name' => $faker->firstName().' '.$faker->lastName().', S.Pd.',
            'password' => 'password', 'peran' => 'guru', 'jenis_guru' => 'mapel', 'status' => 'menunggu',
            'keterangan_daftar' => 'Guru Pendidikan Agama Islam kelas I–III',
        ]);

        if (KegiatanHarian::exists()) {
            return;
        }

        // Murid fiktif
        $kemampuan = [];
        foreach ($kelasList as $kelas) {
            foreach (range(1, 24) as $_) {
                $jk = $faker->randomElement(['L', 'P']);
                $murid = Murid::create([
                    'nama' => $faker->firstName($jk === 'L' ? 'male' : 'female').' '.$faker->lastName(),
                    'jenis_kelamin' => $jk,
                    'nis' => (string) $faker->unique()->numberBetween(10000, 99999),
                ]);
                $kelas->murid()->attach($murid->id);
                $kemampuan[$murid->id] = $faker->randomFloat(2, 0.55, 0.98);
            }
        }

        $jenisKegiatan = JenisKegiatan::pluck('id');
        $hari = Rekap::hariKegiatan(Carbon::parse('2026-07-13'), today()->subDay());

        DB::transaction(function () use ($kelasList, $hari, $kemampuan, $jenisKegiatan, $mapel) {
            foreach ($kelasList as $kelas) {
                $murid = $kelas->murid()->pluck('murid.id');
                foreach ($hari as $tgl) {
                    // Kelas 3A sengaja punya beberapa hari belum dicatat
                    if ($kelas->nama === '3A' && $tgl->gte(today()->subDays(6)) && mt_rand(0, 1)) {
                        continue;
                    }

                    $pengisi = $kelas->tingkat >= 4 && mt_rand(1, 10) === 1 ? $mapel->id : $kelas->wali_kelas_id;
                    $kegiatan = KegiatanHarian::create([
                        'kelas_id' => $kelas->id, 'tanggal' => $tgl->toDateString(),
                        'dibuat_oleh' => $pengisi, 'diubah_oleh' => $pengisi,
                    ]);
                    $kegiatan->riwayat()->create(['user_id' => $pengisi, 'aksi' => 'membuat', 'created_at' => $tgl->copy()->setTime(7, mt_rand(15, 50))]);
                    $kegiatan->jenisKegiatan()->attach($jenisKegiatan->random(mt_rand(1, 3)));

                    $baris = [];
                    foreach ($murid as $id) {
                        $p = $kemampuan[$id];
                        $hadir = mt_rand() / mt_getrandmax() < min(0.99, $p + 0.12);
                        $acak = fn (float $f) => $hadir && mt_rand() / mt_getrandmax() < $p * $f;
                        $baris[] = [
                            'kegiatan_harian_id' => $kegiatan->id, 'murid_id' => $id, 'hadir' => $hadir,
                            'konsisten' => $acak(0.95), 'jurnal' => $acak(0.9), 'memahami' => $acak(0.9),
                            'merangkum' => $acak(0.8), 'menyampaikan' => $acak(0.75),
                            'created_at' => now(), 'updated_at' => now(),
                        ];
                    }
                    DB::table('kegiatan_harian_murid')->insert($baris);
                }
            }
        });

        // Kasus pembinaan
        $pelanggaran = JenisPelanggaran::pluck('id');
        $tindakan = TindakanPembinaan::pluck('id');
        $kondisi = ['Sering mengganggu teman saat membaca', 'Tidak membawa buku bacaan', 'Sering datang terlambat ke kegiatan', 'Berbicara kurang sopan kepada teman', 'Tidak mengisi jurnal literasi', 'Mencoret-coret buku perpustakaan'];
        foreach ($kelasList as $kelas) {
            foreach ($kelas->murid()->reorder()->inRandomOrder()->limit(mt_rand(2, 4))->get() as $murid) {
                $tanggal = today()->subDays(mt_rand(3, 75));
                $kasus = KasusPembinaan::create([
                    'murid_id' => $murid->id, 'kelas_id' => $kelas->id, 'tanggal' => $tanggal,
                    'kondisi_awal' => $kondisi[array_rand($kondisi)],
                    'jenis_pelanggaran_id' => $pelanggaran->random(),
                    'tingkat' => ['ringan', 'ringan', 'sedang', 'berat'][mt_rand(0, 3)],
                    'tindakan_pembinaan_id' => $tindakan->random(),
                    'komitmen' => mt_rand(0, 1) ? 'Saya akan membaca dengan tertib dan tidak mengganggu teman.' : null,
                    'user_id' => $kelas->wali_kelas_id,
                ]);
                $urutan = ['belum', 'mulai', 'positif', 'konsisten'];
                $level = 0;
                foreach (range(1, $kasus->mingguDimulai()) as $minggu) {
                    // Minggu berjalan sengaja dibiarkan kosong pada sebagian kasus agar muncul pengingat
                    if ($minggu === $kasus->mingguDimulai() && $minggu < 4 && mt_rand(0, 1)) {
                        break;
                    }
                    $level = max(0, min(3, $level + mt_rand(-1, 2) - ($minggu === 1 ? 1 : 0)));
                    $kasus->pemantauan()->create(['minggu' => $minggu, 'tingkat' => $urutan[$level], 'user_id' => $kelas->wali_kelas_id]);
                }
                if ($kasus->pemantauan()->count() === 4 && mt_rand(0, 3)) {
                    $kasus->update(['status_akhir' => $urutan[$level]]);
                }
            }
        }

        // Karya literasi
        $judul = ['Petualangan Si Kancil', 'Pohon Mangga di Halaman Sekolah', 'Sahabat Sejati', 'Menjaga Kebersihan Kelas', 'Kisah Lutung Kasarung', 'Bumi Rumah Kita', 'Ayo Gemar Membaca', 'Cita-citaku'];
        foreach ($kelasList as $kelas) {
            $jenis = JenisKarya::whereIn('jenjang', [$kelas->jenjang(), 'semua'])->pluck('id');
            foreach (range(1, mt_rand(4, 8)) as $_) {
                $karya = KaryaLiterasi::create([
                    'kelas_id' => $kelas->id, 'tanggal' => today()->subDays(mt_rand(1, 80)),
                    'jenis_karya_id' => $jenis->random(), 'judul' => $judul[array_rand($judul)],
                    'buku_sumber' => mt_rand(0, 1) ? 'Buku cerita rakyat Nusantara' : null,
                    'user_id' => $kelas->wali_kelas_id,
                ]);
                $karya->murid()->attach($kelas->murid()->reorder()->inRandomOrder()->limit(mt_rand(1, 3))->pluck('murid.id'));
            }
        }
        KaryaLiterasi::create([
            'kelas_id' => $kelasList->first()->id, 'tanggal' => today()->subDays(10), 'jenis_karya_id' => JenisKarya::where('nama', 'Puisi')->value('id'),
            'judul' => 'Buku adalah Jendela', 'guru_pemilik_id' => $kelasList->first()->wali_kelas_id, 'user_id' => $kelasList->first()->wali_kelas_id,
        ]);

        // Konten publik
        Konten::firstOrCreate(['slug' => 'tentang-tebak-aksi'], [
            'jenis' => 'halaman', 'judul' => 'Tentang TEBAK AKSI', 'terbit' => true, 'tampil_menu' => true, 'terbit_pada' => now(),
            'ringkasan' => 'TEras Baca Kelas Asah Karakter dan literaSI: program inovasi literasi sekolah.',
            'isi' => '<p>TEBAK AKSI adalah program inovasi literasi UPT SPF SD Inpres Mallengkeri Bertingkat yang memadukan kebiasaan membaca 15 menit setiap hari, pembuatan karya literasi, dan pembinaan karakter murid.</p><p>Setiap hari murid membaca, merangkum, dan menyampaikan kembali isi bacaan. Guru mendampingi dan mencatat perkembangan murid agar sekolah dapat melihat kemajuan literasi dan karakter secara utuh.</p>',
        ]);
        Konten::firstOrCreate(['slug' => 'pekan-literasi-oktober'], [
            'jenis' => 'berita', 'judul' => 'Pekan Literasi Bulan Oktober', 'terbit' => true, 'terbit_pada' => now()->subDays(3),
            'ringkasan' => 'Murid kelas I–VI memamerkan karya literasi terbaik mereka.',
            'isi' => '<p>Dalam rangka Bulan Bahasa, murid memamerkan puisi, poster, dan komik hasil bacaan mereka di Teras Baca masing-masing kelas.</p>',
        ]);

        Pengaduan::firstOrCreate(['isi' => 'Apakah orang tua boleh menyumbangkan buku bacaan untuk Teras Baca kelas?'], [
            'nama' => 'Orang tua murid', 'kategori' => 'pertanyaan', 'status' => 'baru',
        ]);
    }
}
