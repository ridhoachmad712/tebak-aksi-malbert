<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatPengisian extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'riwayat_pengisian';

    protected $fillable = ['kegiatan_harian_id', 'user_id', 'aksi', 'created_at'];

    public function kegiatanHarian(): BelongsTo
    {
        return $this->belongsTo(KegiatanHarian::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
