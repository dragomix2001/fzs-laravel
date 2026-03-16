<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ocenjivanje extends Model
{
    protected $table = 'ocenjivanje';

    protected $fillable = [
        'student_id',
        'aktivnost_id',
        'bodovi',
        'ocena',
        'napomena',
        'profesor_id',
    ];

    protected $casts = [
        'bodovi' => 'decimal:2',
        'ocena' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Kandidat::class, 'student_id');
    }

    public function aktivnost()
    {
        return $this->belongsTo(Aktivnost::class);
    }

    public function profesor()
    {
        return $this->belongsTo(Profesor::class);
    }
}
