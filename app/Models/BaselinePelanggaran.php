<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BaselinePelanggaran extends Model
{
    protected $table = 'baseline_pelanggaran';

    protected $fillable = ['tahun_ajaran_id', 'semester', 'jumlah'];

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }
}
