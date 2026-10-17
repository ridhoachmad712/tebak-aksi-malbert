<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KegiatanHarianMurid extends Model
{
    protected $table = 'kegiatan_harian_murid';

    protected $fillable = ['kegiatan_harian_id', 'murid_id', 'hadir', 'konsisten', 'jurnal', 'memahami', 'merangkum', 'menyampaikan'];

    protected function casts(): array
    {
        return array_fill_keys(array_keys(KegiatanHarian::INDIKATOR), 'boolean');
    }

    public function kegiatanHarian(): BelongsTo
    {
        return $this->belongsTo(KegiatanHarian::class);
    }

    public function murid(): BelongsTo
    {
        return $this->belongsTo(Murid::class);
    }
}
