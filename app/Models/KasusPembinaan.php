<?php

namespace App\Models;

use App\Casts\Tanggal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class KasusPembinaan extends Model
{
    protected $table = 'kasus_pembinaan';

    protected $fillable = [
        'murid_id', 'kelas_id', 'tanggal', 'kondisi_awal', 'jenis_pelanggaran_id', 'jenis_pelanggaran_lain',
        'tingkat', 'tindakan_pembinaan_id', 'tindakan_pembinaan_lain', 'komitmen', 'status_akhir', 'user_id',
    ];

    public const TINGKAT = [
        'ringan' => 'Ringan',
        'sedang' => 'Sedang',
        'berat' => 'Berat',
    ];

    public const KETERANGAN_TINGKAT = [
        'ringan' => 'Cukup dengan teguran/pengingatan dan pembiasaan',
        'sedang' => 'Perlu pendampingan dan pembinaan lebih lanjut',
        'berat' => 'Perlu penanganan sesuai tata tertib dan prosedur sekolah',
    ];

    /** Skala perkembangan untuk pemantauan mingguan dan status akhir. */
    public const PERKEMBANGAN = [
        'belum' => ['label' => 'Belum berubah', 'singkat' => 'Belum'],
        'mulai' => ['label' => 'Mulai berubah', 'singkat' => 'Mulai'],
        'positif' => ['label' => 'Berubah positif', 'singkat' => 'Positif'],
        'konsisten' => ['label' => 'Konsisten positif', 'singkat' => 'Konsisten'],
    ];

    protected function casts(): array
    {
        return ['tanggal' => Tanggal::class];
    }

    public function murid(): BelongsTo
    {
        return $this->belongsTo(Murid::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function jenisPelanggaran(): BelongsTo
    {
        return $this->belongsTo(JenisPelanggaran::class);
    }

    public function tindakanPembinaan(): BelongsTo
    {
        return $this->belongsTo(TindakanPembinaan::class);
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pemantauan(): HasMany
    {
        return $this->hasMany(PemantauanKasus::class)->orderBy('minggu');
    }

    public function scopeDalamPembinaan(Builder $query): Builder
    {
        return $query->whereNull('status_akhir');
    }

    public function namaPelanggaran(): string
    {
        return $this->jenisPelanggaran?->nama ?? $this->jenis_pelanggaran_lain ?? '-';
    }

    public function namaTindakan(): string
    {
        return $this->tindakanPembinaan?->nama ?? $this->tindakan_pembinaan_lain ?? '-';
    }

    public function labelStatus(): string
    {
        if (! $this->status_akhir) {
            return 'Dalam pembinaan';
        }

        return self::PERKEMBANGAN[$this->status_akhir]['label'];
    }

    public function mulaiMinggu(int $minggu): Carbon
    {
        return $this->tanggal->copy()->addDays(7 * ($minggu - 1) + 1);
    }

    /** Jumlah minggu pemantauan yang sudah dimulai (0-4). Minggu 1 dimulai sehari setelah kasus dicatat. */
    public function mingguDimulai(?Carbon $hari = null): int
    {
        $selisih = (int) $this->tanggal->diffInDays($hari ?? today(), false);

        return $selisih < 1 ? 0 : min(4, (int) ceil($selisih / 7));
    }

    /** Minggu yang sudah dimulai tetapi belum diisi pemantauannya. */
    public function mingguBelumDipantau(?Carbon $hari = null): array
    {
        $sampai = $this->mingguDimulai($hari);

        if ($this->status_akhir || $sampai === 0) {
            return [];
        }

        return array_values(array_diff(range(1, $sampai), $this->pemantauan->pluck('minggu')->all()));
    }

    public function perluDipantau(?Carbon $hari = null): bool
    {
        return $this->mingguBelumDipantau($hari) !== [];
    }

    /** Simpan perkembangan satu minggu pemantauan; catatan lama tetap dipakai bila tidak diberikan. */
    public function catatPemantauan(int $minggu, string $tingkat, ?string $catatan = null): void
    {
        abort_unless($minggu >= 1 && $minggu <= $this->mingguDimulai(), 422);
        abort_unless(array_key_exists($tingkat, self::PERKEMBANGAN), 422);

        $data = ['tingkat' => $tingkat, 'user_id' => auth()->id()];
        if ($catatan !== null) {
            $data['catatan'] = trim($catatan) ?: null;
        }
        $this->pemantauan()->updateOrCreate(['minggu' => $minggu], $data);
        $this->load('pemantauan');
    }

    /** Semua minggu sudah dipantau tetapi status akhir belum ditetapkan. */
    public function siapDitutup(): bool
    {
        return ! $this->status_akhir && $this->pemantauan->count() >= 4;
    }
}
