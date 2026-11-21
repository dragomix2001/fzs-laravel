<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZapisnikOPolaganju_Student extends Model
{
    protected $table = 'zapisnik_o_polaganju__student';

    protected $fillable = ['kandidat_id', 'prijavaIspita_id', 'zapisnik_id'];

    public function prijava(): BelongsTo
    {
        return $this->belongsTo(PrijavaIspita::class, 'prijavaIspita_id');
    }
}
