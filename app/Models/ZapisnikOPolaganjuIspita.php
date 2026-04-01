<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ZapisnikOPolaganjuIspita extends Model
{
    use HasFactory;
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $table = 'zapisnik_o_polaganju_ispita';

    protected $casts = ['datum'];

    protected $fillable = ['kandidat_id', 'predmet_id', 'rok_id', 'brojPolaganja', 'datum', 'vreme', 'ucionica', 'prijavaIspita_id', 'profesor_id'];

    public function predmet(): BelongsTo
    {
        return $this->belongsTo(Predmet::class, 'predmet_id');
    }

    public function ispitniRok(): BelongsTo
    {
        return $this->belongsTo(AktivniIspitniRokovi::class, 'rok_id');
    }

    public function studenti(): HasMany
    {
        return $this->hasMany(ZapisnikOPolaganju_Student::class, 'zapisnik_id');
    }

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(Profesor::class, 'profesor_id');
    }
}
