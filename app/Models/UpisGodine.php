<?php

namespace App\Models;

use App\Traits\Auditable;

class UpisGodine extends AndroModel
{
    use Auditable;

    protected $table = 'upis_godine';

    protected $casts = [
        'datumUpisa' => 'datetime',
        'datumPromene' => 'datetime',
    ];

    public function status()
    {
        return $this->belongsTo(StatusGodine::class, 'statusGodine_id');
    }
}
