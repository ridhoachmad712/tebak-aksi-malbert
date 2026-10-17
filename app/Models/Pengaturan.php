<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class Pengaturan extends Model
{
    public $timestamps = false;

    public $incrementing = false;

    protected $table = 'pengaturan';

    protected $primaryKey = 'kunci';

    protected $keyType = 'string';

    protected $fillable = ['kunci', 'nilai'];

    /** Nilai bawaan seluruh pengaturan (target, patokan, identitas sekolah). */
    public const BAWAAN = [
        'target_murid_aktif' => 85,
        'target_guru_aktif' => 85,
        'target_keberhasilan_pembinaan' => 80,
        'target_penurunan_pelanggaran' => 20,
        'target_budaya_membaca' => 85,
        'target_kemampuan_literasi' => 85,
        'batas_murid_aktif' => 75,
        'batas_guru_aktif' => 75,
        'batas_centang_cetak' => 75,
        'batas_sangat_baik' => 86,
        'batas_baik' => 76,
        'batas_cukup' => 60,
        // Guru boleh mengubah catatan mulai tanggal ini; bila kosong, 7 hari ke belakang (batas_ubah_hari lama)
        'ubah_mulai_tanggal' => '',
        'batas_ubah_hari' => 7,
        'nama_sekolah' => 'UPT SPF SD Inpres Mallengkeri Bertingkat',
        'nama_kepala_sekolah' => '',
        'nip_kepala_sekolah' => '',
        'alamat_sekolah' => '',
        'kota_ttd' => 'Makassar',
        'logo_sekolah' => '',
        'kontak_sekolah' => '',
        'cadangan_terakhir' => '',

        // Tampilan situs (diatur admin di menu Tampilan Situs)
        'tema_warna' => 'jingga',
        'medsos_instagram' => '',
        'medsos_facebook' => '',
        'medsos_youtube' => '',
        'medsos_whatsapp' => '',
        'beranda_judul' => 'Lima belas menit membaca, setiap hari, di teras kelas.',
        'beranda_paragraf' => 'TEBAK AKSI mengajak murid membaca, merangkum, dan menceritakan kembali bacaannya, sementara guru membina karakter mereka. Semuanya dicatat setiap hari, sehingga kemajuan setiap kelas terlihat jelas.',
        'beranda_tombol1_teks' => 'Lihat kegiatan murid',
        'beranda_tombol1_tujuan' => 'galeri',
        'beranda_tombol2_teks' => 'Tentang TEBAK AKSI',
        'beranda_tombol2_tujuan' => 'halaman:tentang-tebak-aksi',
        'beranda_foto_id' => '',
        'beranda_tampil_angka' => true,
        'beranda_tampil_alur' => true,
        'beranda_tampil_galeri' => true,
        'beranda_tampil_jalan_pintas' => true,
        'beranda_tampil_berita' => true,
        'alur_judul' => 'Yang terjadi di setiap sesi Teras Baca',
        'alur_langkah' => [
            ['judul' => 'Membaca 15 menit', 'keterangan' => 'Murid memilih buku sendiri dan membaca dengan tenang, lalu mencatatnya di jurnal literasi.'],
            ['judul' => 'Memahami dan merangkum', 'keterangan' => 'Murid menemukan ide pokok bacaan dan menuliskannya dengan bahasa sendiri.'],
            ['judul' => 'Menyampaikan', 'keterangan' => 'Murid menceritakan kembali isi bacaan kepada teman atau guru.'],
            ['judul' => 'Membina karakter', 'keterangan' => 'Bila ada pelanggaran, guru membina murid dan memantau perubahannya setiap minggu selama empat minggu.'],
        ],
        'menu_publik' => [
            ['tujuan' => 'beranda', 'label' => 'Beranda', 'tampil' => true],
            ['tujuan' => 'halaman:tentang-tebak-aksi', 'label' => 'Tentang', 'tampil' => true],
            ['tujuan' => 'berita', 'label' => 'Berita', 'tampil' => true],
            ['tujuan' => 'galeri', 'label' => 'Galeri', 'tampil' => true],
            ['tujuan' => 'diseminasi', 'label' => 'Diseminasi', 'tampil' => true],
            ['tujuan' => 'manual', 'label' => 'Manual Book', 'tampil' => true],
            ['tujuan' => 'pengaduan', 'label' => 'Pengaduan', 'tampil' => true],
        ],
    ];

    /** Halaman tetap yang bisa dipakai sebagai tujuan menu dan tombol. */
    public const TUJUAN_TETAP = [
        'beranda' => 'Beranda',
        'berita' => 'Berita',
        'galeri' => 'Galeri',
        'diseminasi' => 'Diseminasi',
        'manual' => 'Manual Book',
        'pengaduan' => 'Layanan Pengaduan',
        'masuk' => 'Masuk / aplikasi guru',
    ];

    public static function semua(): array
    {
        return Cache::rememberForever('pengaturan', fn () => array_merge(
            self::BAWAAN,
            static::query()->pluck('nilai', 'kunci')->all(),
        ));
    }

    public static function ambil(string $kunci, mixed $bawaan = null): mixed
    {
        return self::semua()[$kunci] ?? $bawaan;
    }

    public static function angka(string $kunci): float
    {
        return (float) self::ambil($kunci, 0);
    }

    /** Tanggal paling awal catatan yang masih boleh diubah guru (tidak melewati hari ini). */
    public static function ubahMulai(): Carbon
    {
        $tanggal = self::ambil('ubah_mulai_tanggal');
        try {
            $mulai = filled($tanggal) ? Carbon::createFromFormat('!Y-m-d', substr((string) $tanggal, 0, 10)) : null;
        } catch (\Throwable) {
            $mulai = null;
        }

        return ($mulai ?? today()->subDays((int) self::angka('batas_ubah_hari')))->min(today());
    }

    public static function aktif(string $kunci): bool
    {
        return filter_var(self::ambil($kunci, false), FILTER_VALIDATE_BOOLEAN);
    }

    /** Pengaturan berbentuk daftar (disimpan sebagai JSON). */
    public static function daftar(string $kunci): array
    {
        $nilai = self::ambil($kunci, []);

        return is_array($nilai) ? $nilai : (json_decode((string) $nilai, true) ?: []);
    }

    /** Pilihan tujuan tombol/menu: halaman tetap dan halaman konten yang terbit. */
    public static function pilihanTujuan(): array
    {
        $halaman = Konten::terbit()->where('jenis', 'halaman')->orderBy('urutan')->pluck('judul', 'slug')
            ->mapWithKeys(fn ($judul, $slug) => ['halaman:'.$slug => 'Halaman: '.$judul])->all();

        return self::TUJUAN_TETAP + $halaman;
    }

    /** Alamat dari kode tujuan; null bila tujuan sudah tidak ada (misalnya halaman dihapus). */
    public static function urlTujuan(?string $tujuan): ?string
    {
        if (! $tujuan) {
            return null;
        }

        if (str_starts_with($tujuan, 'halaman:')) {
            $slug = substr($tujuan, 8);

            return Konten::terbit()->where('slug', $slug)->exists() ? route('halaman', $slug) : null;
        }

        return match ($tujuan) {
            'beranda' => route('beranda'),
            'berita' => route('berita'),
            'galeri' => route('galeri'),
            'diseminasi' => route('diseminasi'),
            'manual' => route('manual'),
            'pengaduan' => route('pengaduan'),
            'masuk' => route('login'),
            default => null,
        };
    }

    public static function simpan(array $data): void
    {
        foreach ($data as $kunci => $nilai) {
            $nilai = match (true) {
                is_array($nilai) => json_encode(array_values($nilai)),
                is_bool($nilai) => $nilai ? '1' : '0',
                default => $nilai,
            };
            static::updateOrCreate(['kunci' => $kunci], ['nilai' => $nilai]);
        }

        Cache::forget('pengaturan');
    }
}
