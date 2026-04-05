<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mesto extends Model
{
    use HasFactory;

    protected $table = 'mesto';

    public function opstina()
    {
        return $this->belongsTo(Opstina::class, 'opstina_id');
    }
}
