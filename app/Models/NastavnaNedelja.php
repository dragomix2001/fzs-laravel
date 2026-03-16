<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NastavnaNedelja extends Model
{
    protected $table = 'nastavne_nedelje';

    protected $fillable = [
        'skolska_godina_id',
        'redni_broj',
        'datum_pocetka',
        'datum_kraja',
    ];

    public function skolskaGodina()
    {
        return $this->belongsTo(SkolskaGodUpisa::class, 'skolska_godina_id');
    }

    public function prisanstva()
    {
        return $this->hasMany(Prisanstvo::class);
    }
}
