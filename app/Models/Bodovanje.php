<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bodovanje extends Model
{
    use HasFactory;

    protected $table = 'bodovanje';

    protected $fillable = [
        'opisnaOcena',
        'poeniMin',
        'poeniMax',
        'ocena',
        'indikatorAktivan',
    ];
}
