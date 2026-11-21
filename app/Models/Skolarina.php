<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Skolarina extends Model
{
    protected $table = 'skolarina';

    protected $fillable = [
        'kandidat_id',
        'iznos',
        'komentar',
        'godinaStudija_id',
        'tipStudija_id',
        'studijskiProgram_id',
    ];

    protected $casts = ['datum' => 'date'];

    public function kandidat(): BelongsTo
    {
        return $this->belongsTo(Kandidat::class, 'kandidat_id');
    }

    public function uplate(): HasMany
    {
        return $this->hasMany(UplataSkolarine::class, 'skolarina_id');
    }

    public function tipStudija(): BelongsTo
    {
        return $this->belongsTo(TipStudija::class, 'tipStudija_id');
    }

    public function godinaStudija(): BelongsTo
    {
        return $this->belongsTo(GodinaStudija::class, 'godinaStudija_id');
    }
}
