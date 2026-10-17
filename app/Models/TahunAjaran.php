<?php

namespace App\Models;

use App\Casts\Tanggal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TahunAjaran extends Model
{
    protected $table = 'tahun_ajaran';

    protected $fillable = ['nama', 'mulai', 'selesai', 'aktif'];

    protected function casts(): array
    {
        return ['mulai' => Tanggal::class, 'selesai' => Tanggal::class, 'aktif' => 'boolean'];
    }

    protected static function booted(): void
    {
        // Hanya satu tahun ajaran yang aktif
        static::saved(function (TahunAjaran $ta) {
            if ($ta->aktif) {
                static::whereKeyNot($ta->id)->where('aktif', true)->update(['aktif' => false]);
            }
        });
    }

    public static function aktif(): ?self
    {
        return static::where('aktif', true)->first();
    }

    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class);
    }
}
