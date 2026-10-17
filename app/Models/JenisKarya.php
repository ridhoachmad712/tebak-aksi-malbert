<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class JenisKarya extends Model
{
    protected $table = 'jenis_karya';

    protected $fillable = ['nama', 'jenjang', 'aktif', 'urutan'];

    public const JENJANG = [
        'rendah' => 'Kelas Rendah (I–III)',
        'tinggi' => 'Kelas Tinggi (IV–VI)',
        'semua' => 'Semua Kelas',
    ];

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
