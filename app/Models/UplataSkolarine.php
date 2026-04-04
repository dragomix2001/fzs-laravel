<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UplataSkolarine extends Model
{
    protected $table = 'uplata_skolarine';

    protected $casts = [
        'datum' => 'date',
    ];

    protected $guarded = ['formatDatum'];

    public function kandidat(): BelongsTo
    {
        return $this->belongsTo(Kandidat::class, 'kandidat_id');
    }
}
