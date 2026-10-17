<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TindakanPembinaan extends Model
{
    protected $table = 'tindakan_pembinaan';

    protected $fillable = ['nama', 'aktif', 'urutan'];

    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
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
