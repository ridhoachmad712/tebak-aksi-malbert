<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemantauanKasus extends Model
{
    protected $table = 'pemantauan_kasus';

    protected $fillable = ['kasus_pembinaan_id', 'minggu', 'tingkat', 'catatan', 'user_id'];

    public function kasus(): BelongsTo
    {
        return $this->belongsTo(KasusPembinaan::class, 'kasus_pembinaan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
