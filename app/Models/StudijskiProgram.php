<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudijskiProgram extends Model
{
    use HasFactory;

    protected $table = 'studijski_program';

    protected $fillable = ['naziv', 'skrNaziv', 'indikatorAktivan'];

    public function tipStudija(): BelongsTo
    {
        return $this->belongsTo(TipStudija::class, 'tipStudija_id');
    }
}
