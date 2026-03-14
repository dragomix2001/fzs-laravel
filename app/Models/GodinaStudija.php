<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GodinaStudija extends Model
{
    protected $table = 'godina_studija';
    
    protected $casts = [
        'datumUpisa' => 'datetime',
        'datumPromene' => 'datetime',
    ];
}
