<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'peran', 'jenis_guru', 'status', 'telepon', 'nip', 'keterangan_daftar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const PERAN = [
        'admin' => 'Admin',
        'kepsek' => 'Kepala Sekolah',
        'guru' => 'Guru',
    ];

    public const JENIS_GURU = [
        'kelas' => 'Guru Kelas',
        'mapel' => 'Guru Mata Pelajaran',
    ];

    public const STATUS = [
        'menunggu' => 'Menunggu Persetujuan',
        'aktif' => 'Aktif',
        'nonaktif' => 'Nonaktif',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->aktif() && $this->isAdmin();
    }

    public function aktif(): bool
    {
        return $this->status === 'aktif';
    }

    public function isAdmin(): bool
    {
        return $this->peran === 'admin';
    }

    public function isKepsek(): bool
    {
        return $this->peran === 'kepsek';
    }

    public function isGuru(): bool
    {
        return $this->peran === 'guru';
    }

    public function kelasWali(): HasMany
    {
        return $this->hasMany(Kelas::class, 'wali_kelas_id');
    }

    public function kelasMapel(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'kelas_guru', 'user_id', 'kelas_id');
    }

    public function scopeGuru(Builder $query): Builder
    {
        return $query->where('peran', 'guru');
    }

    /**
     * Kelas di tahun ajaran aktif yang boleh dicatat oleh pengguna ini.
     *
     * @return Collection<int, Kelas>
     */
    public function kelasAkses(): Collection
    {
        $query = Kelas::query()->aktif()->orderBy('tingkat')->orderBy('nama');

        if (! $this->isGuru()) {
            return $query->get();
        }

        return $query->where(function (Builder $q) {
            $q->where('wali_kelas_id', $this->id)
                ->orWhereHas('guruMapel', fn (Builder $g) => $g->where('users.id', $this->id));
        })->get();
    }

    public function bolehAksesKelas(Kelas|int $kelas): bool
    {
        $id = $kelas instanceof Kelas ? $kelas->id : $kelas;

        return $this->kelasAkses()->contains('id', $id);
    }
}
