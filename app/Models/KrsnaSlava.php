<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KrsnaSlava extends Model
{
    use HasFactory;

    protected $table = 'krsna_slava';

    protected $fillable = ['naziv', 'datumSlave', 'indikatorAktivan'];
}
