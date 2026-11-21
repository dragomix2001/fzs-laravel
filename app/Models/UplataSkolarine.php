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

    protected $fillable = [
        'skolarina_id',
        'kandidat_id',
        'iznos',
        'naziv',
        'datum',
    ];

    public function kandidat(): BelongsTo
    {
        return $this->belongsTo(Kandidat::class, 'kandidat_id');
    }
}
