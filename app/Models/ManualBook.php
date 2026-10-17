<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ManualBook extends Model
{
    protected $table = 'manual_book';

    protected $fillable = ['judul', 'deskripsi', 'file', 'untuk', 'urutan'];

    public const UNTUK = [
        'publik' => 'Publik (semua pengunjung)',
        'guru' => 'Khusus guru (setelah login)',
    ];

    public function urlFile(): ?string
    {
        return $this->file ? Storage::disk('public')->url($this->file) : null;
    }
}
