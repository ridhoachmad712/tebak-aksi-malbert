<?php

namespace App\Models;

use App\Casts\Tanggal;
use Illuminate\Database\Eloquent\Model;

class HariLibur extends Model
{
    protected $table = 'hari_libur';

    protected $fillable = ['tanggal', 'keterangan'];

    protected function casts(): array
    {
        return ['tanggal' => Tanggal::class];
    }
}
