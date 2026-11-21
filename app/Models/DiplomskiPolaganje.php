<?php

namespace App\Models;

use Database\Factories\DiplomskiPoljanjeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiplomskiPolaganje extends Model
{
    use HasFactory;

    protected $table = 'diplomski_polaganje';

    protected $casts = ['datum' => 'date'];

    protected $fillable = ['kandidat_id', 'tipStudija_id', 'studijskiProgram_id', 'predmet_id', 'nazivTeme', 'datum', 'vreme',
        'profesor_id', 'profesor_id_predsednik', 'profesor_id_clan', 'rok_id', 'brojBodova', 'ocena'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Kandidat::class, 'kandidat_id');
    }

    public function predmet(): BelongsTo
    {
        return $this->belongsTo(PredmetProgram::class, 'predmet_id');
    }

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(Profesor::class, 'profesor_id');
    }

    public function ispitniRok(): BelongsTo
    {
        return $this->belongsTo(AktivniIspitniRokovi::class, 'rok_id');
    }

    public function clan(): BelongsTo
    {
        return $this->belongsTo(Profesor::class, 'profesor_id_clan');
    }

    public function predsednik(): BelongsTo
    {
        return $this->belongsTo(Profesor::class, 'profesor_id_predsednik');
    }

    protected static function newFactory(): DiplomskiPoljanjeFactory
    {
        return DiplomskiPoljanjeFactory::new();
    }
}
