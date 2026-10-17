# TEBAK AKSI

**TEras Baca Kelas Asah Karakter dan literaSI**: aplikasi pencatatan program inovasi literasi UPT SPF SD Inpres Mallengkeri Bertingkat.

Kebutuhan lengkap aplikasi ada di [KEBUTUHAN-APLIKASI.md](KEBUTUHAN-APLIKASI.md), dan pedoman tampilan di [DESIGN.md](DESIGN.md).

## Isi aplikasi

| Bagian | Alamat | Pengguna |
|---|---|---|
| Halaman publik (profil, berita, galeri, diseminasi, manual book, pengaduan) | `/` | Semua pengunjung |
| Masuk / daftar guru | `/masuk`, `/daftar` | Guru |
| Beranda, Kegiatan Harian, Pembinaan, Karya, Rekap | `/guru/...` | Guru (admin juga bisa) |
| Monev | `/monev` | Kepala sekolah, admin |
| Panel admin | `/admin` | Admin |

## Teknologi

Laravel 13 (PHP 8.3+), Livewire 4, Filament 5 (panel admin), Tailwind CSS 4, MySQL/MariaDB (produksi) atau SQLite (pengembangan), dompdf (PDF), OpenSpout (Excel).

## Menjalankan di komputer sendiri

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Buka `http://127.0.0.1:8000`.

`migrate --seed` mengisi **data awal** (akun admin dan daftar baku) dan, di luar mode produksi, **data contoh fiktif** (6 kelas, 144 murid fiktif, catatan sejak Juli 2026). Semua nama di data contoh dibuat acak, bukan data murid asli.

Akun contoh (semua memakai kata sandi `password`, **hanya untuk uji coba lokal**):

| Peran | Email |
|---|---|
| Admin | `admin@tebakaksi.test` |
| Kepala sekolah | `kepsek@tebakaksi.test` |
| Guru kelas I–VI | `guru1@tebakaksi.test` … `guru6@tebakaksi.test` |
| Guru PJOK (kelas IV–VI) | `pjok@tebakaksi.test` |
| Pendaftar menunggu persetujuan | `pendaftar@tebakaksi.test` |

Menjalankan tes:

```bash
php artisan test
```

## Memasang di Hostinger (hPanel + SSH)

Aset tampilan (`public/build`) ikut tersimpan di repositori, jadi server **tidak perlu Node.js**.

1. **Siapkan hPanel**
   - *Websites → Advanced → PHP Configuration*: pilih PHP **8.3** atau lebih baru; pastikan ekstensi `gd`, `intl`, `zip`, `pdo_mysql`, `fileinfo` aktif.
   - *Databases → MySQL Databases*: buat database dan pengguna, lalu catat nama database, nama pengguna, dan kata sandinya.
2. **Ambil kode lewat SSH**
   ```bash
   cd ~
   git clone <alamat-repositori> tebak-aksi
   cd tebak-aksi
   composer install --no-dev --optimize-autoloader
   cp .env.example .env
   php artisan key:generate
   ```
3. **Isi `.env`** (pakai `nano .env`):
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://domain-sekolah.sch.id
   SESSION_SECURE_COOKIE=true
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_DATABASE=...
   DB_USERNAME=...
   DB_PASSWORD=...
   ADMIN_EMAIL=email-admin-sekolah
   ADMIN_PASSWORD=kata-sandi-awal-yang-kuat
   ```
4. **Siapkan database dan berkas**
   ```bash
   php artisan migrate --force
   php artisan db:seed --class=DataAwalSeeder --force
   php artisan storage:link
   php artisan filament:assets
   php artisan optimize
   ```
   Di mode produksi tidak ada data contoh yang dibuat. Setelah masuk pertama kali, segera ganti kata sandi admin.
   Aktifkan juga SSL gratis di hPanel (*Security → SSL*). Bila `APP_URL` diawali `https://`, aplikasi otomatis memakai https untuk semua tautan.
5. **Arahkan domain ke folder `public`**
   ```bash
   cd ~/domains/domain-sekolah.sch.id
   mv public_html public_html_lama
   ln -s ~/tebak-aksi/public public_html
   ```
6. **Isi data awal lewat panel admin** (`/admin`): Tahun Ajaran (aktifkan) → Kelas → Guru & Pengguna → Murid (bisa impor Excel) → Pengaturan Target & Patokan (nama kepala sekolah, baseline pelanggaran).

**Tahun ajaran baru**: di panel admin, menu Tahun Ajaran, tekan *Mulai tahun ajaran baru*. Kelas disalin, murid naik ke tingkat berikutnya, dan murid kelas VI dinonaktifkan. Catatan tahun lama tetap tersimpan.

**Memperbarui aplikasi** di kemudian hari:

```bash
cd ~/tebak-aksi
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize
```

**Cadangan data**: admin dapat mengunduh cadangan dari panel admin, menu *Pengaturan → Cadangan Data* (data dalam berkas .sql, foto dan berkas dalam .zip). Aktifkan juga cadangan otomatis di hPanel (*Files → Backups*).

## Format impor murid

Berkas `.xlsx` atau `.csv` dengan baris pertama berisi judul kolom:

| Nama | NIS | NISN | JK |
|---|---|---|---|
| (wajib) | (opsional) | (opsional) | L / P |
