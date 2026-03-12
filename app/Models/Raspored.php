<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Raspored extends Model
{
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

    public function predmet()
    {
        return $this->belongsTo(Predmet::class);
    }

    public function profesor()
    {
        return $this->belongsTo(Profesor::class);
    }

    public function studijskiProgram()
    {
        return $this->belongsTo(StudijskiProgram::class, 'studijski_program_id');
    }

    public function godinaStudija()
    {
        return $this->belongsTo(GodinaStudija::class, 'godina_studija_id');
    }

    public function semestar()
    {
        return $this->belongsTo(Semestar::class);
    }

    public function skolskaGodina()
    {
        return $this->belongsTo(SkolskaGodUpisa::class, 'skolska_godina_id');
    }

    public function oblikNastave()
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
