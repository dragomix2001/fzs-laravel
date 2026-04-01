<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipStudija extends Model
{
    use HasFactory;
    protected $table = 'tip_studija';

    protected $fillable = ['naziv', 'skrNaziv', 'indikatorAktivan'];

    public function studijskiProgram()
    {
        return $this->hasMany(StudijskiProgram::class, 'tipStudija_id');
    }
}
