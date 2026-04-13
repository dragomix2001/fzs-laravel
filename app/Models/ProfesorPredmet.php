<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfesorPredmet extends Model
{
    protected $table = 'profesor_predmet';

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(Profesor::class, 'profesor_id');
    }

    public function predmet(): BelongsTo
    {
        return $this->belongsTo(PredmetProgram::class, 'predmet_id');
    }

    public function semestar(): BelongsTo
    {
        return $this->belongsTo(Semestar::class, 'semestar_id');
    }

    public function oblik_nastave(): BelongsTo
    {
        return $this->belongsTo(OblikNastave::class, 'oblik_nastave_id');
    }
}
