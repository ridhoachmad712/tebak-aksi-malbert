<?php

namespace App\Models;

use App\Casts\Tanggal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class KaryaLiterasi extends Model
{
    protected $table = 'karya_literasi';

    protected $fillable = ['kelas_id', 'tanggal', 'jenis_karya_id', 'jenis_karya_lain', 'judul', 'buku_sumber', 'guru_pemilik_id', 'user_id'];

    protected function casts(): array
    {
        return ['tanggal' => Tanggal::class];
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function jenisKarya(): BelongsTo
    {
        return $this->belongsTo(JenisKarya::class);
    }

    public function murid(): BelongsToMany
    {
        return $this->belongsToMany(Murid::class, 'karya_murid', 'karya_literasi_id', 'murid_id');
    }

    public function guruPemilik(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_pemilik_id');
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function foto(): MorphMany
    {
        return $this->morphMany(Foto::class, 'fotoable');
    }

    /** Bulan karya dikunci admin: karya tidak bisa diubah atau dihapus. */
    public function terkunci(): bool
    {
        return Pengaturan::terkunci($this->tanggal);
    }

    public function namaJenis(): string
    {
        return $this->jenisKarya?->nama ?? $this->jenis_karya_lain ?? '-';
    }

    public function namaPemilik(): string
    {
        return $this->guruPemilik?->name ?? $this->murid->pluck('nama')->join(', ');
    }
}
