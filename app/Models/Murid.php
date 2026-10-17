<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Murid extends Model
{
    protected $table = 'murid';

    protected $fillable = ['nama', 'nis', 'nisn', 'jenis_kelamin', 'aktif'];

    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }

    public function kelas(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'kelas_murid', 'murid_id', 'kelas_id');
    }

    public function kelasAktif(): ?Kelas
    {
        return $this->kelas()->aktif()->first();
    }

    public function kasusPembinaan(): HasMany
    {
        return $this->hasMany(KasusPembinaan::class);
    }
}
