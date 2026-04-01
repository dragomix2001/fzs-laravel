<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiplomskiPrijavaOdbrane extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public $table = 'diplomski_prijava_odbrane';

    public $dates = ['datumPrijave', 'datumOdbrane'];

    public $fillable = ['kandidat_id', 'tipStudija_id', 'studijskiProgram_id', 'predmet_id', 'nazivTeme', 'datumPrijave',
        'datumOdbrane', 'indikatorOdobreno', 'temu_odobrio_profesor_id', 'odbranu_odobrio_profesor_id'];

    public function predmet(): BelongsTo
    {
        return $this->belongsTo(PredmetProgram::class, 'predmet_id');
    }

    public function odobrioTemuProfesor(): BelongsTo
    {
        return $this->belongsTo(Profesor::class, 'temu_odobrio_profesor_id');
    }

    public function odobrioOdbranuProfesor(): BelongsTo
    {
        return $this->belongsTo(Profesor::class, 'odbranu_odobrio_profesor_id');
    }
}
