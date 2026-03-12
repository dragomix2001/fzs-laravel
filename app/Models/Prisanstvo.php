<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prisanstvo extends Model
{
    protected $table = 'prisanstva';
    
    protected $fillable = [
        'student_id',
        'predmet_id',
        'nastavna_nedelja_id',
        'status',
        'napomena',
        'profesor_id',
    ];

    public function student()
    {
        return $this->belongsTo(Kandidat::class, 'student_id');
    }

    public function predmet()
    {
        return $this->belongsTo(Predmet::class);
    }

    public function nastavnaNedelja()
    {
        return $this->belongsTo(NastavnaNedelja::class);
    }

    public function profesor()
    {
        return $this->belongsTo(Profesor::class);
    }
}
