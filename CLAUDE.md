# TEBAK AKSI: panduan pengembangan

Aplikasi internal sekolah dasar untuk mencatat kegiatan Teras Baca. Kebutuhan dan seluruh keputusan produk ada di `KEBUTUHAN-APLIKASI.md`; ikuti dokumen itu.

## Prinsip yang wajib dijaga

- **Tidak ada isian yang terisi sendiri.** Semua centang dilakukan guru. Aplikasi hanya menyediakan tombol bantu (mis. "Centang semua") dan menghitung rekap dari isian guru.
- Kemudahan guru adalah prioritas utama: sedikit menu, ketuk bukan ketik, bahasa sehari-hari, nyaman di laptop dan HP.
- Seluruh antarmuka, nama tabel, model, dan variabel domain memakai **bahasa Indonesia**.
- Data murid adalah data anak: jangan pernah memakai data asli di seeder/tes; folder `Docs/` (berisi data asli) tidak masuk git.
- **Tampilan mengikuti `DESIGN.md`**: tanpa emoji, gradasi, blur, atau bayangan pada panel; satu aksen jingga untuk tindakan utama; keadaan kosong selalu menyebut sebab dan langkah berikutnya.
- **Data yang punya catatan tidak boleh hilang**: relasi ke kelas, murid, tahun ajaran, dan daftar baku memakai `restrictOnDelete`; penghapusan di admin lewat `App\Filament\Support\HapusAman`.

## Struktur

- `app/Services/Rekap.php`: semua perhitungan rekap dan indikator Monev. Pakai `rekapBanyakKelas()` untuk banyak kelas sekaligus; hasil berulang disimpan per permintaan lewat `ingat()`. Hari kegiatan = Senin–Jumat dikurangi `hari_libur`, mulai dari awal tahun ajaran aktif, tidak melewati hari ini.
- `app/Services/LaporanBaku.php` + `RekapUnduhController`: 4 laporan baku sekolah ke PDF/Excel.
- `app/Livewire/Guru/*`: halaman guru (layout `components.layouts.guru`). `Monev` untuk kepsek/admin.
- `app/Filament/*`: panel admin (`/admin`, hanya peran admin). Login tetap lewat `/masuk`.
- `app/Services/TahunAjaranBaru.php`: wizard tahun ajaran baru (salin kelas, naikkan murid, luluskan kelas VI).
- `app/Models/Pengaturan.php`: semua target/patokan (nilai bawaan di `BAWAAN`), diatur admin di halaman Pengaturan.
- Kolom tanggal memakai cast `App\Casts\Tanggal` (disimpan `Y-m-d`) agar konsisten di SQLite dan MySQL.

## Catatan teknis

- Laravel 13, Livewire 4 (komponen berbasis kelas), Filament 5, Tailwind 4. Kelas CSS bersama (`kartu`, `tombol-utama`, `isian`, dst.) ada di `resources/css/app.css`.
- Closure Filament diinjeksi berdasarkan nama parameter: pakai `$query`, `$record`, `$data`, bukan singkatan.
- `public/build` sengaja ikut di-commit karena hosting (Hostinger) tidak menjalankan Node; jalankan `npm run build` sebelum commit perubahan tampilan.
- Tes: `php artisan test` (SQLite in-memory).
