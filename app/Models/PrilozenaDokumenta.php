<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $redniBrojDokumenta
 * @property string $naziv
 * @property int $skolskaGodina_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read GodinaStudija|null $godinaStudija
 */
class PrilozenaDokumenta extends Model
{
    use HasFactory;

    protected $table = 'prilozena_dokumenta';

    protected $fillable = ['redniBrojDokumenta', 'naziv', 'skolskaGodina_id'];

    public function kandidatDokumenta(): HasMany
    {
        return $this->hasMany(KandidatPrilozenaDokumenta::class, 'prilozenaDokumenta_id');
    }

    public function kandidati(): BelongsToMany
    {
        return $this->belongsToMany(
            Kandidat::class,
            'kandidat_prilozena_dokumenta',
            'prilozenaDokumenta_id',
            'kandidat_id'
        )
            ->withPivot(['id', 'indikatorAktivan', 'review_status', 'reviewer_id', 'notes', 'reviewed_at'])
            ->withTimestamps();
    }

    public function godinaStudija(): BelongsTo
    {
        return $this->belongsTo(GodinaStudija::class, 'skolskaGodina_id');
    }
}
