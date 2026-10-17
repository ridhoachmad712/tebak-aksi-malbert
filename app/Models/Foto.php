<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Foto extends Model
{
    protected $table = 'foto';

    protected $fillable = ['fotoable_type', 'fotoable_id', 'path', 'keterangan', 'tampil_galeri', 'user_id'];

    protected function casts(): array
    {
        return ['tampil_galeri' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::deleted(fn (Foto $foto) => Storage::disk('public')->delete($foto->path));
    }

    public function fotoable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function sumber(): string
    {
        return match ($this->fotoable_type) {
            KegiatanHarian::class => 'Kegiatan Harian',
            KaryaLiterasi::class => 'Karya Literasi',
            Diseminasi::class => 'Diseminasi',
            default => 'Unggahan Galeri',
        };
    }
}
