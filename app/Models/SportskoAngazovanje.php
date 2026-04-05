<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SportskoAngazovanje extends Model
{
    use HasFactory;

    protected $table = 'sportsko_angazovanje';

    protected $fillable = ['nazivKluba', 'odDoGodina', 'ukupnoGodina', 'sport_id', 'kandidat_id'];

    public function kandidat()
    {
        return $this->belongsTo(Kandidat::class, 'kandidat_id');
    }

    public function sport()
    {
        return $this->belongsTo(Sport::class, 'sport_id');
    }
}
