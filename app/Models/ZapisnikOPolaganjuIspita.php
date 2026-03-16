<?php

namespace App\Models;

class ZapisnikOPolaganjuIspita extends AndroModel
{
    protected $table = 'zapisnik_o_polaganju_ispita';

    protected $casts = ['datum'];

    protected $fillable = ['kandidat_id', 'predmet_id', 'rok_id', 'brojPolaganja', 'datum', 'vreme', 'ucionica', 'prijavaIspita_id', 'profesor_id'];

    public function predmet()
    {
        return $this->belongsTo(Predmet::class, 'predmet_id');
    }

    public function ispitniRok()
    {
        return $this->belongsTo(AktivniIspitniRokovi::class, 'rok_id');
    }

    public function studenti()
    {
        return $this->hasMany(ZapisnikOPolaganju_Student::class, 'zapisnik_id');
    }

    public function profesor()
    {
        return $this->belongsTo(Profesor::class, 'profesor_id');
    }
}
