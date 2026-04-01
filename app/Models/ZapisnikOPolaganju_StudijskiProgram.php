<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZapisnikOPolaganju_StudijskiProgram extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $table = 'zapisnik_o_polaganju__studijski_program';
}
