<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read Opstina $opstina
 */
class Mesto extends Model
{
    use HasFactory;

    protected $table = 'mesto';

    public function opstina(): BelongsTo
    {
        return $this->belongsTo(Opstina::class, 'opstina_id');
    }
}
