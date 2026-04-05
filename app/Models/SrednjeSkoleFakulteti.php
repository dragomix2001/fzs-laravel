<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SrednjeSkoleFakulteti extends Model
{
    use HasFactory;

    protected $table = 'srednje_skole_fakulteti';

    protected $fillable = ['naziv', 'indSkoleFakulteta'];
}
