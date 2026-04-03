<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrijavaIspita extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $table = 'prijava_ispita';

    protected $casts = [
        'datum' => 'datetime',
    ];

    protected $fillable = ['kandidat_id', 'predmet_id', 'rok_id', 'profesor_id', 'brojPolaganja', 'datum', 'tipPrijave_id'];

    public function kandidat(): BelongsTo
    {
        return $this->belongsTo(Kandidat::class, 'kandidat_id');
    }

    public function predmet(): BelongsTo
    {
        return $this->belongsTo(PredmetProgram::class, 'predmet_id');
    }

    public function rok(): BelongsTo
    {
        return $this->belongsTo(AktivniIspitniRokovi::class, 'rok_id');
    }

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(Profesor::class, 'profesor_id');
    }

    public static function nazivRokaPoId($idRoka)
    {
        $rok = AktivniIspitniRokovi::find($idRoka);
        if ($rok == null) {
            return null;
        }

        return $rok->naziv;
    }
}
