<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZapisnikOPolaganju_StudijskiProgram extends Model
{
    protected $table = 'zapisnik_o_polaganju__studijski_program';

    protected $fillable = ['zapisnik_id', 'StudijskiProgram_id'];
}
