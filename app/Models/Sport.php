<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sport extends Model
{
    protected $table = 'sport';

    protected $fillable = ['naziv', 'indikatorAktivan'];
}
