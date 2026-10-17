# Dokumen Kebutuhan Aplikasi TEBAK AKSI

**TEras Baca Kelas Asah Karakter dan literaSI**
UPT SPF SD Inpres Mallengkeri Bertingkat

| | |
|---|---|
| Versi | 1.1 (disepakati, dibangun) |
| Tanggal | 17 Oktober 2026 |
| Cakupan | Tahap 1 |

---

## 1. Tujuan

TEBAK AKSI membantu guru **mencatat kegiatan Teras Baca harian** dalam satu rangkaian: membaca, memahami dan merangkum bacaan, menyampaikan informasi, menghasilkan karya, serta pembinaan karakter.

Dari catatan harian tersebut, sekolah **otomatis memperoleh laporan** perkembangan literasi, karakter, dan pelaksanaan program, tanpa guru perlu menyusun rekap manual.

Aplikasi menjawab tiga pertanyaan sekolah:

1. Apakah kegiatan Teras Baca benar-benar terlaksana?
2. Apakah kemampuan literasi murid berkembang?
3. Apakah pembinaan karakter berhasil mengubah perilaku murid?

## 2. Prinsip Desain

1. **Mudah dipahami tanpa pelatihan panjang.** Beranda guru selalu menunjukkan pekerjaan berikutnya.
2. **Ketuk, bukan ketik.** Sebagian besar isian berupa centang atau pilihan.
3. **Tidak ada isian yang terisi sendiri.** Semua centang dilakukan guru. Aplikasi hanya menyediakan tombol bantu seperti "Centang semua", dan menghitung rekap dari isian guru.
4. **Dua pola pencatatan saja:**
   - **Harian:** Kegiatan Harian (tabel centang)
   - **Saat ada kejadian:** Kasus Pembinaan dan Karya Literasi
5. **Laptop sebagai utama, nyaman di HP.** Di HP, tabel berubah menjadi kartu per murid dengan tombol besar.
6. **Tampilan sederhana dan ceria.** Bahasa sehari-hari guru, warna cerah, ikon jelas.

## 3. Pengguna dan Hak Akses

| Peran | Cara mendapat akses | Yang bisa dilakukan |
|---|---|---|
| **Pengunjung publik** | Tanpa login | Melihat halaman publik, mengirim pengaduan |
| **Guru Kelas** | Daftar sendiri lalu **disetujui admin**, atau dibuatkan admin | Mencatat untuk **1 kelas** miliknya |
| **Guru Mata Pelajaran** | Sama seperti guru kelas | Mencatat untuk kelas yang **ditugaskan admin** (bisa lebih dari satu) |
| **Kepala Sekolah** | Dibuatkan admin | Melihat laporan Monev semua kelas |
| **Admin** | Akun awal sistem | Mengelola seluruh data dan pengaturan |

Catatan:
- Akun guru yang mendaftar sendiri berstatus **Menunggu Persetujuan** dan belum bisa masuk sampai disetujui admin.
- Guru yang memegang lebih dari satu kelas memilih kelas di beranda sebelum mencatat.
- Semua guru (kelas maupun mapel) bisa mencatat Kasus Pembinaan dan Karya Literasi untuk murid di kelas yang menjadi aksesnya.

## 4. Peta Aplikasi

```
🌐 HALAMAN PUBLIK (tanpa login)
   ├── Beranda / Profil Program TEBAK AKSI
   ├── Konten tambahan (dikelola admin: berita, informasi, dll.)
   ├── Galeri Dokumentasi TEBAK AKSI
   ├── Diseminasi
   ├── Manual Book
   ├── Layanan Pengaduan
   └── Masuk / Daftar Guru

👩‍🏫 HALAMAN GURU
   ├── Beranda (pekerjaan hari ini + pengingat)
   ├── Kegiatan Harian
   ├── Kasus Pembinaan
   ├── Karya Literasi
   └── Rekap Kelas Saya

📊 HALAMAN KEPALA SEKOLAH
   └── Monev (laporan & indikator keberhasilan)

🛠️ HALAMAN ADMIN
   ├── Kelola Guru (persetujuan, penugasan kelas)
   ├── Kelola Kelas & Murid
   ├── Data Master (jenis pelanggaran, tindakan pembinaan, jenis karya,
   │                jenis kegiatan, aspek karakter)
   ├── Pengaturan Target & Patokan
   ├── Kalender Kegiatan (hari libur)
   ├── Pengaduan Masuk
   ├── Konten Publik, Galeri, Diseminasi, Manual Book
   └── Monev (sama seperti kepala sekolah)
```

---

## 5. Fitur Guru

### 5.1 Beranda Guru

Beranda menampilkan **satu tombol utama** sesuai keadaan hari itu:
- *"Mulai Kegiatan Hari Ini"* jika catatan hari ini belum dibuat
- *"Lanjutkan Kegiatan Hari Ini"* jika sudah dimulai
- *"✓ Kegiatan hari ini sudah dicatat"* jika sudah diisi

Di bawahnya terdapat **pengingat**:
- 🔔 Murid yang perlu dipantau minggu ini (Kasus Pembinaan)
- ⚠️ Hari kegiatan yang belum dicatat
- Ringkasan singkat kelas bulan ini

### 5.2 Kegiatan Harian

Hari kegiatan: **Senin–Jumat**, kecuali hari libur yang ditandai admin.

**Satu catatan bersama per kelas per hari.** Guru kelas dan guru mapel yang punya akses ke kelas tersebut membuka dan melanjutkan catatan yang sama.

**Tabel centang per murid:**

| Nama | Hadir | Konsisten | Jurnal | Memahami | Merangkum | Menyampaikan Informasi |
|---|:-:|:-:|:-:|:-:|:-:|:-:|
| | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ |

Kelompok kolom:
- **Budaya Membaca:** Hadir, Konsisten, Jurnal
- **Kemampuan Literasi:** Memahami, Merangkum, Menyampaikan Informasi

Aturan:
1. Semua kotak **kosong** saat catatan dibuka. Guru mencentang sendiri.
2. Tombol **"Centang semua"** tersedia per kolom dan hanya bekerja saat ditekan guru.
3. Kolom selain Hadir **tidak bisa dicentang** sebelum Hadir dicentang.
4. Setiap kolom punya **keterangan singkat** (ikon ⓘ) tentang arti centang:

| Kolom | Arti ✓ |
|---|---|
| Hadir | Mengikuti kegiatan membaca 15 menit secara aktif |
| Konsisten | Membaca fokus dan tertib selama ±15 menit, tidak mengganggu teman |
| Jurnal | Mencatat hasil membaca di jurnal literasi |
| Memahami | Mampu menyebutkan ide pokok atau informasi penting dari bacaan |
| Merangkum | Menuliskan gagasan utama dengan bahasa sendiri |
| Menyampaikan Informasi | Menceritakan kembali isi bacaan dengan runtut |

**Isian opsional di catatan harian:**
- **Jenis kegiatan** hari ini (boleh pilih lebih dari satu): Membaca 15 menit, Diskusi buku, Menulis/rangkuman, Membuat karya, Refleksi karakter, Presentasi karya. Daftar diatur admin.
- **Foto dokumentasi** (boleh lebih dari satu). Foto dapat dipilih admin untuk tampil di Galeri publik.

**Jejak pengisian:** setiap perubahan mencatat siapa dan kapan, misalnya *"Diisi Bu Ani · 07.30 — diubah Pak Budi (PJOK) · 10.15"*.

### 5.3 Kasus Pembinaan

Dicatat **hanya saat ada pelanggaran**.

**① Catat Kasus**

| Isian | Bentuk | Wajib |
|---|---|:-:|
| Murid | Pilih dari daftar | ✓ |
| Tanggal kejadian | Tanggal (bawaan: hari ini) | ✓ |
| Kondisi Awal | Teks singkat | ✓ |
| Jenis Pelanggaran | Pilih dari daftar admin, atau tulis sendiri | ✓ |
| Tingkat | Ringan / Sedang / Berat | ✓ |
| Tindakan Pembinaan | Pilih dari daftar admin, atau tulis sendiri | ✓ |
| Komitmen murid | Teks singkat | – |

Keterangan tingkat:
- **Ringan:** cukup diselesaikan dengan teguran/pengingatan dan pembiasaan
- **Sedang:** memerlukan pendampingan dan pembinaan lebih lanjut
- **Berat:** memerlukan penanganan sesuai tata tertib dan prosedur sekolah

**② Pantau Mingguan** (4 kali; Minggu 1 dihitung sejak kasus dicatat)

Setiap minggu guru memilih satu tingkat perkembangan, dengan catatan opsional:

| | Tingkat |
|---|---|
| 🟥 | Belum berubah |
| 🟨 | Mulai berubah |
| 🟩 | Berubah positif |
| ⭐ | Konsisten positif |

**③ Status Akhir**

Dipilih guru dari 4 tingkat yang sama. Selama belum ditetapkan, kasus berstatus **Dalam Pembinaan**.

**Catatan:** jenis pelanggaran yang ditulis sendiri oleh guru dapat diusulkan admin untuk dimasukkan ke daftar baku.

### 5.4 Karya Literasi

Dicatat **saat ada karya**.

| Isian | Bentuk | Wajib |
|---|---|:-:|
| Pemilik karya | Satu/beberapa murid (karya kelompok), **atau guru sendiri** | ✓ |
| Tanggal | Tanggal (bawaan: hari ini) | ✓ |
| Jenis karya | Pilih dari daftar admin, atau tulis sendiri | ✓ |
| Judul karya | Teks | ✓ |
| Judul buku sumber | Teks | – |
| Foto karya | Unggah gambar | – |

Admin dapat memilih karya untuk ditampilkan di **Galeri publik**.

### 5.5 Rekap Kelas Saya

Guru melihat rekap bulanan untuk kelas yang menjadi aksesnya, dengan format yang sama seperti laporan Monev (bagian 6), terbatas pada kelasnya sendiri.

---

## 6. Monev (Kepala Sekolah & Admin)

### 6.1 Filter

Bulan, semester, tahun pelajaran, dan kelas (atau semua kelas).

### 6.2 Skala Kategori (dipakai di seluruh aplikasi)

| Nilai | Kategori | Warna |
|---|---|---|
| 86–100% | Sangat Baik | 🟢 |
| 76–85% | Baik | 🔵 |
| 60–75% | Cukup | 🟡 |
| ≤ 59% | Perlu Bimbingan | 🔴 |

Batas kategori dapat diubah admin.

### 6.3 Laporan Baku

Rekap di layar menampilkan **jumlah centang / jumlah hari kegiatan** beserta warna kategori. Saat dicetak sesuai format sekolah, angka diubah menjadi **✓** jika mencapai batas cetak (bawaan 75%).

**a. Rekapan Budaya Membaca**

| No | Nama Murid | Kelas | Kehadiran Membaca | Konsisten | Kelengkapan Jurnal |
|---|---|---|:-:|:-:|:-:|

**b. Daftar Hadir Kemampuan Literasi**

| No | Nama Murid | Kelas | Memahami | Merangkum | Menyampaikan Informasi |
|---|---|---|:-:|:-:|:-:|

**c. Rekapan Pelanggaran** (satu baris per kasus)

| No | Nama Murid | Kelas | Kondisi Awal | Minggu 1 | Minggu 2 | Minggu 3 | Minggu 4 | Jenis Pelanggaran | Tindakan Pembinaan | Status |
|---|---|---|---|:-:|:-:|:-:|:-:|---|---|---|

**d. Rekapan Data Ekosistem Literat** (murid dan guru)

| No | Nama Murid/Guru | Kelas | Memanfaatkan Teras Baca | Karya Literasi |
|---|---|---|:-:|:-:|

**Format ekspor:** PDF (siap cetak) dan Excel.

### 6.4 Indikator Keberhasilan (Dasbor)

| Indikator | Rumus | Target bawaan |
|---|---|---|
| Murid aktif memanfaatkan Teras Baca | Murid aktif ÷ seluruh murid × 100% | ≥ 85% |
| Guru aktif memanfaatkan Teras Baca | Guru aktif ÷ seluruh guru × 100% | ≥ 85% |
| Keberhasilan pembinaan | Murid binaan berstatus 🟩/⭐ ÷ seluruh murid binaan × 100% | ≥ 80% |
| Penurunan pelanggaran per semester | (Baseline − jumlah semester ini) ÷ baseline × 100% | ≥ 20% |
| Rata-rata Budaya Membaca | Rata-rata % tiga indikator Budaya Membaca | diatur admin |
| Rata-rata Kemampuan Literasi | Rata-rata % tiga indikator Kemampuan Literasi | diatur admin |

Tampilan tambahan:
- **Capaian per kelas I–VI:** jumlah murid, aktif, belum aktif, % aktif
- **Aspek karakter yang paling perlu dibina** per kelas, dari jumlah kasus per aspek
- **Kasus berstatus 🟥 Belum berubah** di akhir masa pemantauan, sebagai daftar tindak lanjut
- **Tren bulanan** kehadiran membaca dan jumlah pelanggaran

---

## 7. Aturan Perhitungan

| Data | Cara hitung |
|---|---|
| **Jumlah hari kegiatan (N)** | Hari Senin–Jumat dalam periode, dikurangi hari libur yang ditandai admin |
| **% per indikator murid** | Jumlah centang ÷ N × 100% |
| **Memanfaatkan Teras Baca (murid)** | Sama dengan jumlah centang **Hadir** |
| **Memanfaatkan Teras Baca (guru)** | Jumlah hari guru tercatat mengisi Kegiatan Harian |
| **Murid aktif** | % Hadir ≥ batas murid aktif (bawaan 75%) |
| **Guru aktif** | % hari mengisi Kegiatan Harian ≥ batas guru aktif (bawaan 75%) |
| **Karya Literasi** | Jumlah karya atas nama murid/guru dalam periode |
| **Aspek karakter per kasus** | Diambil dari aspek yang dihubungkan admin ke jenis pelanggaran |

---

## 8. Fitur Admin

### 8.1 Kelola Guru
- Menyetujui atau menolak pendaftaran guru
- Membuat akun guru dan kepala sekolah
- Menetapkan peran: guru kelas (1 kelas) atau guru mapel (beberapa kelas)
- Menonaktifkan akun

### 8.2 Kelola Kelas & Murid
- Tahun pelajaran dan semester aktif
- Kelas (I–VI beserta rombelnya, misalnya 4A, 4B)
- Data murid: nama, NIS/NISN (opsional), jenis kelamin, kelas
- Impor murid dari Excel
- Pindah kelas dan kenaikan kelas di akhir tahun pelajaran

### 8.3 Data Master

| Data | Isi awal (dapat diubah) |
|---|---|
| **Aspek Karakter** | Disiplin, Tanggung Jawab, Kepedulian, Kerja Sama, Percaya Diri, Kesantunan |
| **Jenis Pelanggaran** + aspek karakternya | Datang terlambat (Disiplin) · Tidak mengikuti kegiatan tanpa alasan (Disiplin) · Tidak mengerjakan tugas (Tanggung Jawab) · Mengganggu kegiatan pembelajaran (Disiplin) · Tidak menjaga kebersihan (Kepedulian) · Tidak menjaga fasilitas/buku (Tanggung Jawab) · Berkata kurang santun (Kesantunan) · Mengganggu atau mengejek teman (Kepedulian) · Tidak mematuhi aturan kelas/Teras Baca (Disiplin) |
| **Tindakan Pembinaan** | Teguran lisan · Nasihat pribadi · Pendampingan membaca · Tugas membaca tambahan · Pemberitahuan orang tua |
| **Jenis Karya** | *Kelas rendah:* gambar bercerita, kalimat sederhana, kartu cerita, komik sederhana · *Kelas tinggi:* rangkuman, cerita pendek, puisi, poster, komik, laporan sederhana, infografis, karya digital |
| **Jenis Kegiatan** | Membaca 15 menit · Diskusi buku · Menulis/rangkuman · Membuat karya · Refleksi karakter · Presentasi karya |

### 8.4 Pengaturan Target & Patokan

Semua angka target dan patokan diatur admin:

| Pengaturan | Nilai bawaan |
|---|---|
| Target murid aktif Teras Baca | 85% |
| Target guru aktif Teras Baca | 85% |
| Target keberhasilan pembinaan | 80% |
| Target penurunan pelanggaran per semester | 20% |
| Target rata-rata Budaya Membaca | (diisi admin) |
| Target rata-rata Kemampuan Literasi | (diisi admin) |
| Batas murid aktif | 75% |
| Batas guru aktif | 75% |
| Batas ✓ saat dicetak | 75% |
| Batas skala kategori | 86 / 76 / 60 |
| Baseline jumlah pelanggaran (per semester) | (diisi admin) |

### 8.5 Kalender Kegiatan
Menandai hari libur atau hari tanpa kegiatan Teras Baca, agar tidak dihitung dalam N.

### 8.6 Pengaduan Masuk
Daftar pengaduan dari halaman publik dengan status **Baru → Diproses → Selesai**, serta catatan tindak lanjut.

### 8.7 Konten Publik
- Halaman dan berita (judul, isi, gambar, terbit/draf)
- Galeri Dokumentasi: kurasi foto dari Kegiatan Harian dan Karya Literasi, atau unggah langsung
- Diseminasi: album dokumentasi kegiatan diseminasi (judul, tanggal, keterangan, foto)
- Manual Book: unggah PDF dan/atau halaman panduan

---

## 9. Halaman Publik

| Halaman | Isi |
|---|---|
| **Beranda** | Profil singkat TEBAK AKSI, statistik umum (misalnya jumlah kegiatan dan karya bulan ini), sorotan galeri dan berita |
| **Konten tambahan** | Halaman/berita buatan admin |
| **Galeri** | Foto kegiatan dan karya terpilih |
| **Diseminasi** | Dokumentasi kegiatan diseminasi |
| **Manual Book** | Panduan penggunaan aplikasi dan program |
| **Layanan Pengaduan** | Formulir: nama (opsional), kontak (opsional), kategori, isi pengaduan |

Statistik publik hanya berupa **angka agregat**, tanpa nama murid.

---

## 10. Teknologi dan Hosting

**Lingkungan:** Hostinger (hPanel), akses SSH, database MySQL di hosting.

| Bagian | Pilihan |
|---|---|
| Bahasa & kerangka kerja | PHP 8.3+ dengan **Laravel** (versi stabil terbaru) |
| Database | MySQL/MariaDB dari Hostinger |
| Halaman guru & publik | Blade + **Livewire** + Alpine.js + **Tailwind CSS** (desain ceria khusus) |
| Halaman admin & Monev | **Filament** (panel admin siap pakai, disesuaikan warnanya) |
| Ekspor | PDF (dompdf) dan Excel |
| Foto | Dikompres otomatis saat diunggah, disimpan di hosting |
| Tugas terjadwal | Cron Job di hPanel (pengingat dan perhitungan rutin) |
| Deploy | Git + SSH: tarik kode, `composer install`, migrasi. Aset CSS/JS dibangun di laptop pengembang sebelum diunggah |

Alasan: Laravel berjalan baik di hosting Hostinger biasa tanpa server khusus, mendukung MySQL bawaan hosting, dan mudah dirawat.

## 11. Keamanan dan Privasi Data Anak

- Semua halaman selain halaman publik **wajib login**.
- Guru hanya melihat data murid di kelas yang menjadi aksesnya.
- Kata sandi disimpan terenkripsi (hash). Tersedia fitur lupa kata sandi melalui admin.
- Halaman publik **tidak menampilkan nama murid** dalam statistik. Foto yang tampil di galeri dipilih admin.
- Formulir pengaduan dilindungi dari spam (pembatasan kiriman dan pengecekan otomatis).
- Cadangan database: memakai cadangan otomatis Hostinger, ditambah tombol unduh cadangan oleh admin.
- Selama pengembangan dan uji coba, data yang dipakai adalah **data contoh**, bukan data murid asli.

## 12. Tahap 2 (di luar cakupan saat ini)

- **Asesmen Berkala** (awal dan akhir semester) dengan rubrik skor 1–4:
  - Kemampuan Literasi (memahami, merangkum, menyampaikan, karya)
  - Karakter (6 aspek)
  - Observasi konsistensi membaca dan pemanfaatan Teras Baca
- Perbandingan sebelum–sesudah dari hasil asesmen
- Jurnal Pembinaan lengkap (alur Observation–Thought–Feeling–Desire)

Rancangan data Tahap 1 sudah menyiapkan tahun pelajaran, semester, murid, dan aspek karakter agar Tahap 2 dapat ditambahkan tanpa membongkar yang sudah ada.

---

## 13. Keputusan Tambahan (disetujui 17 Oktober 2026)

Seluruh usulan bawaan di bawah ini telah disetujui.

| No | Pertanyaan | Keputusan |
|---|---|---|
| 1 | Murid yang **sakit/izin** pada hari kegiatan: apakah tetap dihitung tidak hadir? | Tetap dihitung tidak hadir (✓/tidak saja), agar sederhana |
| 2 | Sampai kapan guru boleh **mengubah catatan hari sebelumnya**? | Guru: maksimal 7 hari ke belakang. Admin: kapan saja |
| 3 | **Guru aktif** dihitung dari kelas mana untuk guru mapel? | Dari semua hari ia tercatat mengisi Kegiatan Harian di kelas mana pun |
| 4 | Apakah murid tampil **berurutan abjad** di tabel harian? | Ya, berurutan abjad |
| 5 | **Kategori pengaduan** apa saja? | Saran, Pertanyaan, Keluhan, Lainnya |
| 6 | Apakah **identitas sekolah** (logo, nama kepala sekolah) dicetak di laporan? | Ya, diatur admin |
