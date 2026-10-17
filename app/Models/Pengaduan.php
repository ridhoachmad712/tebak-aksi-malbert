<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaduan extends Model
{
    protected $table = 'pengaduan';

    protected $fillable = ['nama', 'kontak', 'kategori', 'isi', 'status', 'tindak_lanjut', 'ip'];

    public const KATEGORI = [
        'saran' => 'Saran',
        'pertanyaan' => 'Pertanyaan',
        'keluhan' => 'Keluhan',
        'lainnya' => 'Lainnya',
    ];

    public const STATUS = [
        'baru' => 'Baru',
        'diproses' => 'Diproses',
        'selesai' => 'Selesai',
    ];
}
