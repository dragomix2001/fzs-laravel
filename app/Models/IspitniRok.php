<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IspitniRok extends Model
{
    use HasFactory;

    protected $table = 'ispitni_rok';

    public function aktivniRokovi()
    {
        return $this->hasMany(AktivniIspitniRokovi::class);
    }
}
