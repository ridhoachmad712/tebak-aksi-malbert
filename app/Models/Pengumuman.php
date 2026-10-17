<?php

namespace App\Models;

use App\Casts\Tanggal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    protected $table = 'pengumuman';

    protected $fillable = ['isi', 'tautan', 'sasaran', 'mulai', 'selesai', 'aktif'];

    public const SASARAN = [
        'semua' => 'Halaman publik dan halaman guru',
        'publik' => 'Halaman publik saja',
        'guru' => 'Halaman guru saja',
    ];

    protected function casts(): array
    {
        return ['mulai' => Tanggal::class, 'selesai' => Tanggal::class, 'aktif' => 'boolean'];
    }

    /** Pengumuman yang sedang berlaku hari ini untuk sasaran tertentu. */
    public function scopeBerlaku(Builder $query, string $sasaran): Builder
    {
        $hariIni = today()->toDateString();

        return $query->where('aktif', true)
            ->whereIn('sasaran', [$sasaran, 'semua'])
            ->where('mulai', '<=', $hariIni)
            ->where(fn (Builder $q) => $q->whereNull('selesai')->orWhere('selesai', '>=', $hariIni))
            ->latest('mulai');
    }

    public function sedangBerlaku(): bool
    {
        return $this->aktif && $this->mulai->lte(today()) && (! $this->selesai || $this->selesai->gte(today()));
    }
}
