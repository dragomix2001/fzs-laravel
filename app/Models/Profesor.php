<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profesor extends Model
{
    use HasFactory;
    protected $table = 'profesor';

    public function status()
    {
        return $this->belongsTo(StatusProfesora::class, 'status_id');
    }

    public function angazovanja()
    {
        return $this->hasMany(ProfesorPredmet::class);
    }
}
