<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Raspored extends Model
{
    protected $table = 'raspored';

    protected $fillable = [
        'predmet_id',
        'profesor_id',
        'studijski_program_id',
        'godina_studija_id',
        'semestar_id',
        'skolska_godina_id',
        'oblik_nastave_id',
        'dan',
        'vreme_od',
        'vreme_do',
        'prostorija',
        'grupa',
    ];

    protected $casts = [
        'dan' => 'integer',
        'vreme_od' => 'datetime:H:i',
        'vreme_do' => 'datetime:H:i',
    ];

    public function predmet(): BelongsTo
    {
        return $this->belongsTo(Predmet::class);
    }

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(Profesor::class);
    }

    public function studijskiProgram(): BelongsTo
    {
        return $this->belongsTo(StudijskiProgram::class, 'studijski_program_id');
    }

    public function godinaStudija(): BelongsTo
    {
        return $this->belongsTo(GodinaStudija::class, 'godina_studija_id');
    }

    public function semestar(): BelongsTo
    {
        return $this->belongsTo(Semestar::class);
    }

    public function skolskaGodina(): BelongsTo
    {
        return $this->belongsTo(SkolskaGodUpisa::class, 'skolska_godina_id');
    }

    public function oblikNastave(): BelongsTo
    {
        return $this->belongsTo(OblikNastave::class, 'oblik_nastave_id');
    }

    public function scopeAktivan($query)
    {
        return $query->whereHas('skolskaGodina', function ($q) {
            $q->where('aktivan', true);
        });
    }

    public function scopeZaDan($query, $dan)
    {
        return $query->where('dan', $dan);
    }
}
