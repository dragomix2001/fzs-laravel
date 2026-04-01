<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudijskiProgram extends Model
{
    use HasFactory;

    protected $table = 'studijski_program';

    protected $fillable = ['naziv', 'skrNaziv', 'indikatorAktivan'];

    public function tipStudija()
    {
        return $this->belongsTo(TipStudija::class, 'tipStudija_id');
    }
}
