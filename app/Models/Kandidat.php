<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Kandidat extends Authenticatable
{
    use Auditable, Notifiable;

    protected $table = 'kandidat';

    protected $fillable = [
        'imeKandidata', 'prezimeKandidata', 'jmbg', 'datumRodjenja', 'mestoRodjenja',
        'krsnaSlava_id', 'kontaktTelefon', 'adresaStanovanja', 'email',
        'imePrezimeJednogRoditelja', 'kontaktTelefonRoditelja', 'srednjeSkoleFakulteti',
        'mestoZavrseneSkoleFakulteta', 'smerZavrseneSkoleFakulteta', 'uspehSrednjaSkola_id',
        'opstiUspehSrednjaSkola_id', 'srednjaOcenaSrednjaSkola', 'sportskoAngazovanje_id',
        'telesnaTezina', 'visina', 'prilozenaDokumentaPrvaGodina_id', 'statusUpisa_id',
        'brojBodovaTest', 'brojBodovaSkola', 'ukupniBrojBodova', 'prosecnaOcena',
        'upisniRok', 'brojIndeksa', 'skolskaGodinaUpisa_id', 'indikatorAktivan',
        'studijskiProgram_id', 'tipStudija_id', 'godinaStudija_id', 'mesto_id',
        'uplata', 'upisan', 'drzavaZavrseneSkole', 'drzavaRodjenja', 'godinaZavrsetkaSkole',
        'slika', 'diplomski', 'datumStatusa', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'datumRodjenja' => 'datetime',
        'datumStatusa' => 'datetime',
    ];

    public function angazovanja()
    {
        return $this->hasMany(SportskoAngazovanje::class);
    }

    public function tipStudija()
    {
        return $this->belongsTo(TipStudija::class, 'tipStudija_id');
    }

    public function program()
    {
        return $this->belongsTo(StudijskiProgram::class, 'studijskiProgram_id');
    }

    public function upisaneGodine()
    {
        return $this->hasMany(UpisGodine::class);
    }

    public function prijaveIspita()
    {
        return $this->hasMany(PrijavaIspita::class);
    }

    public function mestoRodjenja()
    {
        return $this->belongsTo(Opstina::class, 'mestoRodjenja_id');
    }

    public function godinaUpisa()
    {
        return $this->belongsTo(SkolskaGodUpisa::class, 'skolskaGodinaUpisa_id');
    }

    public function godinaStudija()
    {
        return $this->belongsTo(GodinaStudija::class, 'godinaStudija_id');
    }

    public function statusUpisa()
    {
        return $this->belongsTo(StatusGodine::class, 'statusUpisa_id');
    }
}