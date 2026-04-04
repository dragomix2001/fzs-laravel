<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Obavestenje extends Model
{
    protected $table = 'obavestenja';

    protected $fillable = [
        'naslov',
        'sadrzaj',
        'tip',
        'aktivan',
        'datum_objave',
        'datum_isteka',
        'profesor_id',
    ];

    protected $casts = [
        'aktivan' => 'boolean',
        'datum_objave' => 'datetime',
        'datum_isteka' => 'datetime',
    ];

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(Profesor::class);
    }

    public function korisnici(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'obavestenja_korisnici')
            ->withPivot('procitano', 'datum_citanja')
            ->withTimestamps();
    }

    public function scopeAktivna($query)
    {
        return $query->where('aktivan', true)
            ->where(function ($q) {
                $q->whereNull('datum_isteka')
                    ->orWhere('datum_isteka', '>=', now());
            });
    }

    public function scopeZaTip($query, $tip)
    {
        return $query->where('tip', $tip);
    }
}
