<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UspehSrednjaSkola extends Model
{
    protected $table = 'uspeh_srednja_skola';

    protected $fillable = [
        'kandidat_id',
        'opstiUspeh_id',
        'srednja_ocena',
        'RedniBrojRazreda',
        'srednjeSkoleFakulteti_id',
    ];

    protected $attributes = [
        'srednjeSkoleFakulteti_id' => 1,
    ];
}
