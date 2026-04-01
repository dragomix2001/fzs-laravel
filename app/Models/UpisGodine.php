<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpisGodine extends Model
{
    use Auditable, HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $table = 'upis_godine';

    protected $casts = [
        'datumUpisa' => 'datetime',
        'datumPromene' => 'datetime',
    ];

    public function status(): BelongsTo
    {
        return $this->belongsTo(StatusGodine::class, 'statusGodine_id');
    }
}
