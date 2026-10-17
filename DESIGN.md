# Pedoman Desain TEBAK AKSI

Acuan tetap untuk setiap perubahan tampilan. Disusun dengan pedoman [anti-slop](https://github.com/miqdadbadjuber/anti-slop): setiap keputusan visual punya alasan, dan hiasan tanpa fungsi tidak dipakai.

## Arah

> Aplikasi kerja harian guru SD dan halaman profil program sekolah, bergaya ruang kelas yang rapi: kertas krem, tinta gelap, satu warna jingga untuk tindakan utama.

| Bagian | ENERGY | RHYTHM | MOTION | Alasan |
|---|---|---|---|---|
| Halaman guru, Monev, admin | 1 (tenang) | 1 (seragam) | 1 (hanya hover/fokus) | Dipakai setiap hari di sela mengajar. Yang penting cepat dibaca, bukan menarik perhatian. |
| Halaman publik | 2 (seimbang) | 2 (ada variasi) | 1 | Memperkenalkan program kepada orang tua dan sekolah lain, tetapi tetap sederhana. |

Setiap layar punya **satu titik fokus**. Contoh: di Beranda guru, kotak "Kegiatan hari ini"; di Kegiatan Harian, tabel centang; di Monev, daftar yang perlu ditindaklanjuti.

## Warna

| Peran | Token | Nilai | Dipakai untuk |
|---|---|---|---|
| Dasar | `krem` | `#fffaf3` | Latar halaman |
| Permukaan | `white` | `#ffffff` | Panel dan tabel |
| Garis | `garis` | `#ece3d6` | Batas panel, pemisah baris |
| Tinta | `slate-800` | | Teks utama |
| Teks pendukung | `slate-600` / `slate-500` | | Keterangan. **Jangan memakai `slate-400` untuk teks**, karena kontrasnya kurang. |
| Aksen | `brand-700` | `#c2410c` | Tombol utama, menu aktif, tautan. Hanya untuk tindakan, bukan hiasan. |
| Literasi | `toska-700` | `#0f766e` | Centang kolom Kemampuan Literasi, sebagai pembeda dari Budaya Membaca |
| Status | emerald / sky / amber / rose | | Hanya untuk kategori capaian (Sangat Baik, Baik, Cukup, Perlu Bimbingan) dan status perkembangan |

Tidak ada gradasi, bulatan blur, efek kaca, atau ubin ikon berwarna-warni.

## Bentuk

- Sudut: `rounded-lg` (8px) untuk tombol, isian, dan pilihan; `rounded-xl` (12px) untuk panel. Bentuk kapsul hanya untuk angka hitungan kecil.
- Bayangan: panel **tanpa bayangan**, cukup garis. Bayangan hanya untuk elemen yang melayang: menu tarik-turun, notifikasi, dan dialog.
- Fokus keyboard: cincin 2px `brand-700` di semua elemen yang bisa diklik.
- Target sentuh minimal 44px di HP.

## Huruf

- **Nunito.** Hurufnya membulat dan ramah, cocok untuk lingkungan sekolah dasar, dan tetap jelas dibaca pada ukuran kecil.
- Judul memakai huruf biasa (bukan kapital semua) dan tebal. Tidak ada label kapital berspasi lebar.

## Motif identitas

Huruf pembentuk akronim **TE**ras **BA**ca **K**elas **A**sah Karakter dan litera**SI** diberi warna aksen. Di halaman depan publik, akronim ini ditulis per baris di atas **kertas bergaris dengan garis margin** (kelas `halaman-jurnal`), meniru buku jurnal literasi yang diisi murid setiap hari. Garis kertas ini satu-satunya pola latar yang dipakai, dan hanya di kartu tersebut.

## Ikon dan emoji

- **Tidak ada emoji di antarmuka.** Makna disampaikan lewat teks.
- Ikon Heroicons (garis) hanya dipakai bila membantu memindai: menu, tombol tindakan, dan status. Ikon tidak dipakai sebagai hiasan judul atau kartu.
- Panah hanya di tombol utama yang membawa ke halaman berikutnya.
- Skala perkembangan pembinaan (Belum berubah, Mulai berubah, Berubah positif, Konsisten positif) ditampilkan sebagai lencana warna + teks melalui komponen `x-perkembangan`, bukan emoji.

## Teks

- Bahasa sehari-hari guru, kalimat pendek, kalimat aktif.
- Tanpa tanda seru beruntun dan tanpa sapaan berlebihan.
- **Keadaan kosong** selalu menyebutkan sebabnya dan langkah berikutnya. Contoh: "Belum ada pelanggaran yang dicatat. Catat saat ada kejadian melalui tombol Catat Pelanggaran."
- **Angka** selalu nyata dari data. Label harus sesuai dengan yang dihitung.

## Data dan grafik

- Judul grafik berupa pertanyaan atau ukuran yang jelas, misalnya "Rata-rata kehadiran membaca per bulan".
- Satu grafik untuk satu ukuran.
- Indikator ditampilkan dalam tabel Capaian / Target / Status, tidak dalam deretan kartu yang sama besar.
