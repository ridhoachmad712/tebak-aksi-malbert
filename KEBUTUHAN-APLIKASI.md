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
| Jenis Pelanggaran | Pilih dari daftar admin, atau tulis sendiri | ✓ |
| Tingkat | Ringan / Sedang / Berat | ✓ |
| Tindakan Pembinaan | Pilih dari daftar admin, atau tulis sendiri | ✓ |
| **Catatan OTFD** | Empat teks singkat hasil berbincang dengan murid: **O**bservation (apa yang guru lihat; tercatat sebagai Kondisi Awal di laporan, bisa memakai saran dari jenis pelanggaran), **T**hought (apa yang dipikirkan murid), **F**eeling (apa yang dirasakan murid), **D**esire (apa yang ingin murid lakukan atau perbaiki; menggantikan Komitmen murid) | – |

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
| Foto karya | Unggah gambar, minimal 1 foto; foto terakhir tidak bisa dihapus sebelum ada pengganti | ✓ |

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
| Murid mencapai Budaya Membaca | Murid yang rata-rata % tiga indikator Budaya Membaca ≥ batas murid aktif ÷ seluruh murid × 100% (sama dengan Dashboard dan laporan hasil inovasi) | ≥ 80% |
| Murid mencapai Kemampuan Literasi | Murid yang rata-rata % tiga indikator Kemampuan Literasi ≥ batas murid aktif ÷ seluruh murid × 100% (sama dengan Dashboard dan laporan hasil inovasi) | ≥ 80% |

Tampilan tambahan:
- **Capaian per kelas I–VI:** jumlah murid, aktif, belum aktif, % aktif
- **Aspek karakter yang paling perlu dibina** per kelas, dari jumlah kasus per aspek
- **Kasus berstatus 🟥 Belum berubah** di akhir masa pemantauan, sebagai daftar tindak lanjut
- **Tren bulanan** kehadiran membaca dan jumlah pelanggaran

---

### 6.4a Bulan yang Tampil Bawaan

Di tampilan kepala sekolah (Dashboard, Monev, Rekap, cetak ringkasan Monev), di tampilan admin untuk Monev, dan di angka halaman depan, bulan yang tampil bawaan adalah **bulan lalu selama bulan berjalan masih punya hari kegiatan tersisa**, agar yang terlihat data satu bulan penuh. Pada hari kegiatan terakhir bulan berjalan, bulan berjalan yang tampil. Pada bulan pertama tahun ajaran, bulan berjalan yang tampil. Guru tetap melihat bulan berjalan. Bulan lain tetap bisa dipilih.

### 6.5 Capaian 5 Aspek Inovasi (Dashboard Kepala Sekolah)

Ditambahkan 21 Oktober 2026 mengikuti laporan hasil inovasi sekolah. Dihitung **per jumlah murid** untuk bulan yang dipilih, dengan target diatur admin di Target & Patokan.

| Aspek | Cara menghitung | Target bawaan |
|---|---|---|
| Budaya Membaca | Murid yang rata-rata Hadir, Konsisten, dan Jurnal-nya ≥ batas murid aktif (75%) | ≥ 80% murid |
| Kemampuan Literasi | Murid yang rata-rata Memahami, Merangkum, dan Menyampaikan-nya ≥ batas murid aktif (75%) | ≥ 80% murid |
| Penguatan Karakter | Penurunan jumlah murid berbeda yang melanggar dibanding bulan sebelumnya, disertai rincian per minggu (1–7, 8–14, 15–21, 22–akhir bulan) | turun ≥ 20% per bulan |
| Kepatuhan Perilaku | Murid binaan bulan itu yang status terakhirnya Berubah positif atau Konsisten positif | ≥ 80% |
| Ekosistem Literat | Murid aktif memanfaatkan Teras Baca, dan murid yang menghasilkan minimal 1 karya dalam bulan itu (tercapai bila keduanya tercapai) | ≥ 85% aktif dan 100% berkarya |

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
| **Karya Literasi** | Semua karya literasi milik murid, langsung tampil setelah dicatat guru tanpa persetujuan admin. Menampilkan foto, judul, nama murid pemilik, jenis karya, kelas, dan tanggal; dapat disaring per bulan dan kelas. Beranda menampilkan 4 karya terbaru yang berfoto. Karya milik guru tidak ditampilkan. |
| **Diseminasi** | Dokumentasi kegiatan diseminasi |
| **Manual Book** | Panduan penggunaan aplikasi dan program |
| **Layanan Pengaduan** | Formulir: nama (opsional), kontak (opsional), kategori, isi pengaduan |

Statistik publik hanya berupa **angka agregat**, tanpa nama murid. Pengecualian: halaman Karya Literasi menampilkan nama murid pemilik karya (keputusan sekolah).

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
- Halaman publik **tidak menampilkan nama murid** dalam statistik. Foto yang tampil di galeri dipilih admin. Pengecualian yang diputuskan sekolah: halaman **Karya Literasi** menampilkan nama murid pemilik karya beserta fotonya, langsung setelah dicatat guru. Guru perlu memastikan foto karya tidak memuat data pribadi lain (alamat, nomor telepon, wajah murid lain).
- Formulir pengaduan dilindungi dari spam (pembatasan kiriman dan pengecekan otomatis).
- Cadangan database: memakai cadangan otomatis Hostinger, ditambah tombol unduh cadangan oleh admin.
- Selama pengembangan dan uji coba, data yang dipakai adalah **data contoh**, bukan data murid asli.

## 12. Tahap 2 (di luar cakupan saat ini)

- **Asesmen Berkala** (awal dan akhir semester) dengan rubrik skor 1–4:
  - Kemampuan Literasi (memahami, merangkum, menyampaikan, karya)
  - Karakter (6 aspek)
  - Observasi konsistensi membaca dan pemanfaatan Teras Baca
- Perbandingan sebelum–sesudah dari hasil asesmen
- Jurnal Pembinaan lengkap (alur Observation–Thought–Feeling–Desire), **dimajukan ke Catatan Perbaikan 19 Oktober 2026 (bagian 14, nomor 1)**

Rancangan data Tahap 1 sudah menyiapkan tahun pelajaran, semester, murid, dan aspek karakter agar Tahap 2 dapat ditambahkan tanpa membongkar yang sudah ada.

---

## 13. Keputusan Tambahan (disetujui 17 Oktober 2026)

Seluruh usulan bawaan di bawah ini telah disetujui.

| No | Pertanyaan | Keputusan |
|---|---|---|
| 1 | Murid yang **sakit/izin** pada hari kegiatan: apakah tetap dihitung tidak hadir? | Tetap dihitung tidak hadir (✓/tidak saja), agar sederhana |
| 2 | Sampai kapan guru boleh **mengubah catatan hari sebelumnya**? | Guru: mulai tanggal yang diatur admin di Pengaturan (bawaan 7 hari ke belakang). Admin: kapan saja |
| 3 | **Guru aktif** dihitung dari kelas mana untuk guru mapel? | Dari semua hari ia tercatat mengisi Kegiatan Harian di kelas mana pun |
| 4 | Apakah murid tampil **berurutan abjad** di tabel harian? | Ya, berurutan abjad |
| 5 | **Kategori pengaduan** apa saja? | Saran, Pertanyaan, Keluhan, Lainnya |
| 6 | Apakah **identitas sekolah** (logo, nama kepala sekolah) dicetak di laporan? | Ya, diatur admin |

---

## 14. Catatan Perbaikan (19 Oktober 2026)

Masukan setelah uji coba. **Status: belum dikerjakan, kecuali yang ditandai Selesai.** Kolom "Rincian rencana" adalah tafsiran awal dan perlu dikonfirmasi pemilik sebelum dikerjakan; hal yang masih perlu dijawab ditandai **Perlu dipastikan**.

| No | Catatan | Rincian rencana | Bagian terkait |
|---|---|---|---|
| 1 | Pelanggaran memakai pendekatan **OTFD** | Pembinaan pelanggaran memakai alur **Observation–Thought–Feeling–Desire** (sebelumnya direncanakan untuk Tahap 2). Saat mencatat atau memantau kasus, guru mengisi: **Observation** (apa yang terlihat), **Thought** (apa yang dipikirkan murid), **Feeling** (apa yang dirasakan murid), **Desire** (apa yang ingin murid lakukan atau perbaiki). **Selesai:** diisi sekali di halaman Catat Pelanggaran, berupa empat teks singkat yang opsional. Observation memakai kolom Kondisi Awal (tetap tampil di laporan baku), Desire menggantikan Komitmen murid. Keempatnya tampil di rincian kasus. | 5.3 Kasus Pembinaan, laporan Pelanggaran |
| 2 | Pratinjau foto yang diunggah di halaman Karya Literasi (di rekap karya) | Foto karya tampil sebagai gambar kecil yang bisa diperbesar, baik di daftar Karya Literasi maupun di rekap (laporan Ekosistem Literat). | 5.4 Karya Literasi, 5.5 Rekap |
| 3 | Warna tombol **Simpan catatan** diubah agar terlihat | Tombol *Simpan catatan* di Kegiatan Harian (isian catatan singkat) saat ini bergaya tombol kedua (putih); diubah menjadi tombol yang lebih menonjol agar guru tidak lupa menekannya. **Selesai:** tombol kini memakai warna aksen (jingga) seperti tombol utama. | 5.2 Kegiatan Harian |
| 4 | Foto dokumentasi bisa diunggah lebih dari satu | Guru dapat memilih beberapa foto sekaligus dan menambah foto lagi tanpa menghapus yang sudah ada. Catatan teknis: kotak unggah sudah mengizinkan banyak berkas; **perlu dipastikan** di HP guru apakah yang terjadi hanya satu foto terpilih atau foto lama tertimpa. | 5.2 Kegiatan Harian (foto dokumentasi) |
| 5 | Bentuk pembinaan berupa karya | Tindakan pembinaan dapat berupa **tugas membuat karya** (misalnya menulis refleksi atau poster). Karya hasil pembinaan dicatat dan dihubungkan dengan kasusnya, sehingga terlihat di rincian kasus. **Perlu dipastikan:** apakah karya pembinaan ikut dihitung di Karya Literasi dan laporan Ekosistem Literat, atau dipisahkan. | 5.3 Kasus Pembinaan, 5.4 Karya Literasi |
| 6 | Pratinjau karya | Halaman pratinjau satu karya: foto (bisa diperbesar), judul, jenis, pemilik, kelas, tanggal, dan buku sumber, tanpa harus membuka formulir ubah. | 5.4 Karya Literasi |
| 7 | Karya murid ditampilkan di halaman depan, terpisah dari dokumentasi kegiatan | **Selesai:** menu dan halaman publik **Karya Literasi** (`/karya-literasi`) menampilkan semua karya milik murid beserta nama murid, langsung setelah guru mencatatnya (tanpa persetujuan admin), dengan saringan bulan dan kelas. Beranda menampilkan 4 karya terbaru. Menu ditambahkan otomatis ke menu publik yang tersimpan (setelah Galeri). | 9. Halaman Publik, 11. Keamanan dan Privasi |
| 8 | Sembunyikan riwayat pengisian | **Selesai:** bagian *Riwayat pengisian* tidak lagi ditampilkan di halaman Kegiatan Harian. Jejak pengisian tetap dicatat di sistem karena dipakai untuk menghitung guru aktif mengisi kegiatan harian di Monev. | 5.2 Kegiatan Harian |

