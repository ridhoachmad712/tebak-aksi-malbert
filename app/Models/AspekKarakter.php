<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AspekKarakter extends Model
{
    protected $table = 'aspek_karakter';

    protected $fillable = ['nama', 'urutan'];

    public function jenisPelanggaran(): HasMany
    {
        return $this->hasMany(JenisPelanggaran::class);
    }

    public function scopeUrut(Builder $query): Builder
    {
        return $query->orderBy('urutan')->orderBy('nama');
    }
}
