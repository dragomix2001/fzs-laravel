<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipPredmeta extends Model
{
    use HasFactory;

    protected $table = 'tip_predmeta';

    protected $fillable = ['naziv', 'skrNaziv', 'indikatorAktivan'];
}
