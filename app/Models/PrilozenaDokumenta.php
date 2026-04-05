<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public function godinaStudija(): BelongsTo
    {
        return $this->belongsTo(GodinaStudija::class, 'skolskaGodina_id');
    }
}
