<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function predmet()
    {
        return $this->belongsTo(Predmet::class);
    }

    public function ocenjivanja()
    {
        return $this->hasMany(Ocenjivanje::class);
    }
}
