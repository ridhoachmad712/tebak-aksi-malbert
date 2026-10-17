<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JenisPelanggaran extends Model
{
    protected $table = 'jenis_pelanggaran';

    protected $fillable = ['nama', 'aspek_karakter_id', 'aktif', 'urutan'];

    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }

    public function aspekKarakter(): BelongsTo
    {
        return $this->belongsTo(AspekKarakter::class);
    }

    public function scopeUrut(Builder $query): Builder
    {
        return $query->orderBy('urutan')->orderBy('nama');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('aktif', true);
    }
}
