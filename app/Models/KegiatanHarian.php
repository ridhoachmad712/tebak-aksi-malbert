<?php

namespace App\Models;

use App\Casts\Tanggal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class KegiatanHarian extends Model
{
    protected $table = 'kegiatan_harian';

    protected $fillable = ['kelas_id', 'tanggal', 'catatan', 'dibuat_oleh', 'diubah_oleh'];

    /** Kolom centang harian beserta label dan arti centangnya. */
    public const INDIKATOR = [
        'hadir' => ['label' => 'Hadir', 'kelompok' => 'Budaya Membaca', 'arti' => 'Mengikuti kegiatan membaca 15 menit secara aktif'],
        'konsisten' => ['label' => 'Konsisten', 'kelompok' => 'Budaya Membaca', 'arti' => 'Membaca fokus dan tertib selama ±15 menit, tidak mengganggu teman'],
        'jurnal' => ['label' => 'Jurnal', 'kelompok' => 'Budaya Membaca', 'arti' => 'Mencatat hasil membaca di jurnal literasi'],
        'memahami' => ['label' => 'Memahami', 'kelompok' => 'Kemampuan Literasi', 'arti' => 'Mampu menyebutkan ide pokok atau informasi penting dari bacaan'],
        'merangkum' => ['label' => 'Merangkum', 'kelompok' => 'Kemampuan Literasi', 'arti' => 'Menuliskan gagasan utama dengan bahasa sendiri'],
        'menyampaikan' => ['label' => 'Menyampaikan Informasi', 'kelompok' => 'Kemampuan Literasi', 'arti' => 'Menceritakan kembali isi bacaan dengan runtut'],
    ];

    protected function casts(): array
    {
        return ['tanggal' => Tanggal::class];
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function catatanMurid(): HasMany
    {
        return $this->hasMany(KegiatanHarianMurid::class);
    }

    public function jenisKegiatan(): BelongsToMany
    {
        return $this->belongsToMany(JenisKegiatan::class, 'kegiatan_harian_jenis', 'kegiatan_harian_id', 'jenis_kegiatan_id');
    }

    public function riwayat(): HasMany
    {
        return $this->hasMany(RiwayatPengisian::class)->latest('created_at')->latest('id');
    }

    public function foto(): MorphMany
    {
        return $this->morphMany(Foto::class, 'fotoable');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function pengubah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diubah_oleh');
    }
}
