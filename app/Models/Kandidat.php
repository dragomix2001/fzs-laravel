<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Kandidat extends Model
{
    use Auditable, HasFactory, Notifiable;

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
        'slika', 'diplomski', 'datumStatusa',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected $casts = [
        'datumRodjenja' => 'datetime',
        'datumStatusa' => 'datetime',
    ];

    public function angazovanja(): HasMany
    {
        return $this->hasMany(SportskoAngazovanje::class);
    }

    public function tipStudija(): BelongsTo
    {
        return $this->belongsTo(TipStudija::class, 'tipStudija_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(StudijskiProgram::class, 'studijskiProgram_id');
    }

    public function upisaneGodine(): HasMany
    {
        return $this->hasMany(UpisGodine::class);
    }

    public function prijaveIspita(): HasMany
    {
        return $this->hasMany(PrijavaIspita::class);
    }

    public function mestoRodjenja(): BelongsTo
    {
        return $this->belongsTo(Opstina::class, 'mestoRodjenja_id');
    }

    public function godinaUpisa(): BelongsTo
    {
        return $this->belongsTo(SkolskaGodUpisa::class, 'skolskaGodinaUpisa_id');
    }

    public function godinaStudija(): BelongsTo
    {
        return $this->belongsTo(GodinaStudija::class, 'godinaStudija_id');
    }

    public function statusUpisa(): BelongsTo
    {
        return $this->belongsTo(StatusGodine::class, 'statusUpisa_id');
    }
}
