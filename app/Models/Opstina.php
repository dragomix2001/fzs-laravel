<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Opstina extends Model
{
    use HasFactory;

    protected $table = 'opstina';

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }
}
