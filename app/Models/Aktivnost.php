<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aktivnost extends Model
{
    protected $table = 'aktivnosti';

    protected $fillable = [
        'predmet_id',
        'naziv',
        'tip',
        'max_bodova',
        'prolaz_bodova',
        'datum',
        'vreme_pocetka',
        'ucionica',
        'napomena',
        'aktivan',
    ];

    protected $casts = [
        'datum' => 'date',
        'vreme_pocetka' => 'datetime:H:i',
        'max_bodova' => 'decimal:2',
        'prolaz_bodova' => 'decimal:2',
        'aktivan' => 'boolean',
    ];

    public function predmet(): BelongsTo
    {
        return $this->belongsTo(Predmet::class);
    }

    public function ocenjivanja(): HasMany
    {
        return $this->hasMany(Ocenjivanje::class);
    }
}
