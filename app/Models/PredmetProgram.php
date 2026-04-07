<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PredmetProgram extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $table = 'predmet_program';

    public function predmet(): BelongsTo
    {
        return $this->belongsTo(Predmet::class, 'predmet_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(StudijskiProgram::class, 'studijskiProgram_id');
    }

    public function godinaStudija(): BelongsTo
    {
        return $this->belongsTo(GodinaStudija::class, 'godinaStudija_id');
    }

    public function tipStudija(): BelongsTo
    {
        return $this->belongsTo(TipStudija::class, 'tipStudija_id');
    }

    public function studijskiProgram(): BelongsTo
    {
        return $this->belongsTo(StudijskiProgram::class, 'studijskiProgram_id');
    }

    public function tipPredmeta(): BelongsTo
    {
        return $this->belongsTo(TipPredmeta::class, 'tipPredmeta_id');
    }

    public function prijaveIspita(): HasMany
    {
        return $this->hasMany(PrijavaIspita::class, 'predmet_id');
    }
}
