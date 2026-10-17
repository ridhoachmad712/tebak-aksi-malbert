<?php

namespace App\Models;

use App\Casts\Tanggal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Diseminasi extends Model
{
    protected $table = 'diseminasi';

    protected $fillable = ['judul', 'tanggal', 'tempat', 'keterangan', 'terbit'];

    protected function casts(): array
    {
        return ['tanggal' => Tanggal::class, 'terbit' => 'boolean'];
    }

    public function foto(): MorphMany
    {
        return $this->morphMany(Foto::class, 'fotoable');
    }
}
