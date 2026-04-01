<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZapisnikOPolaganju_Student extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $table = 'zapisnik_o_polaganju__student';

    public function prijava(): BelongsTo
    {
        return $this->belongsTo(PrijavaIspita::class, 'prijavaIspita_id');
    }
}
