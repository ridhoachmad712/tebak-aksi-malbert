<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    protected $table = 'kelas';

    protected $fillable = ['tahun_ajaran_id', 'tingkat', 'nama', 'wali_kelas_id'];

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wali_kelas_id');
    }

    public function guruMapel(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'kelas_guru', 'kelas_id', 'user_id');
    }

    public function murid(): BelongsToMany
    {
        return $this->belongsToMany(Murid::class, 'kelas_murid', 'kelas_id', 'murid_id')->orderBy('nama');
    }

    public function muridAktif(): BelongsToMany
    {
        return $this->murid()->where('murid.aktif', true);
    }

    public function kegiatanHarian(): HasMany
    {
        return $this->hasMany(KegiatanHarian::class);
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->whereHas('tahunAjaran', fn (Builder $q) => $q->where('aktif', true));
    }

    public function jenjang(): string
    {
        return $this->tingkat <= 3 ? 'rendah' : 'tinggi';
    }

    public function namaLengkap(): string
    {
        return 'Kelas '.$this->nama;
    }
}
