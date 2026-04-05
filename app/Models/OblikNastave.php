<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OblikNastave extends Model
{
    use HasFactory;

    protected $table = 'oblik_nastave';

    protected $fillable = ['naziv', 'skrNaziv', 'indikatorAktivan'];
}
