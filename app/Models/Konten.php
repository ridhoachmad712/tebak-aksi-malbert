<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Konten extends Model
{
    protected $table = 'konten';

    protected $fillable = ['jenis', 'judul', 'slug', 'ringkasan', 'isi', 'gambar', 'terbit', 'tampil_menu', 'terbit_pada', 'urutan'];

    public const JENIS = [
        'berita' => 'Berita',
        'halaman' => 'Halaman',
    ];

    protected function casts(): array
    {
        return ['terbit' => 'boolean', 'tampil_menu' => 'boolean', 'terbit_pada' => 'datetime'];
    }

    public function scopeTerbit(Builder $query): Builder
    {
        return $query->where('terbit', true);
    }

    public function urlGambar(): ?string
    {
        return $this->gambar ? Storage::disk('public')->url($this->gambar) : null;
    }
}
