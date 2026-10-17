<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
        'batas_ubah_hari' => 7,
        'nama_sekolah' => 'UPT SPF SD Inpres Mallengkeri Bertingkat',
        'nama_kepala_sekolah' => '',
        'nip_kepala_sekolah' => '',
        'alamat_sekolah' => '',
        'kota_ttd' => 'Makassar',
        'logo_sekolah' => '',
        'kontak_sekolah' => '',
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

    public static function simpan(array $data): void
    {
        foreach ($data as $kunci => $nilai) {
            static::updateOrCreate(['kunci' => $kunci], ['nilai' => $nilai]);
        }

        Cache::forget('pengaturan');
    }
}
