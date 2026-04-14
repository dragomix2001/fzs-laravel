<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prisanstvo extends Model
{
    protected $table = 'prisanstva';

    protected $fillable = [
        'student_id',
        'predmet_id',
        'nastavna_nedelja_id',
        'status',
        'napomena',
        'profesor_id',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Kandidat::class, 'student_id');
    }

    public function predmet(): BelongsTo
    {
        return $this->belongsTo(Predmet::class);
    }

    public function nastavnaNedelja(): BelongsTo
    {
        return $this->belongsTo(NastavnaNedelja::class);
    }

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(Profesor::class);
    }
}
