<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiplomskiPrijavaTeme extends Model
{
    use HasFactory;

    protected $table = 'diplomski_prijava_teme';

    protected $casts = ['datum' => 'date'];

    protected $fillable = ['kandidat_id', 'tipStudija_id', 'studijskiProgram_id', 'predmet_id', 'nazivTeme', 'datum', 'indikatorOdobreno', 'profesor_id'];

    public function predmet(): BelongsTo
    {
        return $this->belongsTo(PredmetProgram::class, 'predmet_id');
    }

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(Profesor::class, 'profesor_id');
    }
}
