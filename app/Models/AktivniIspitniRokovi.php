<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AktivniIspitniRokovi extends Model
{
    use Auditable, HasFactory;

    protected $table = 'aktivni_ispitni_rokovi';

    protected $casts = [
        'pocetak' => 'datetime:Y-m-d',
        'kraj' => 'datetime:Y-m-d',
    ];

    protected $fillable = ['rok_id', 'naziv', 'pocetak', 'kraj', 'komentar', 'tipRoka_id', 'indikatorAktivan'];

    public function nadredjeniRok(): BelongsTo
    {
        return $this->belongsTo(IspitniRok::class, 'rok_id');
    }

    public static function tipRoka(int $id): string
    {
        return match ($id) {
            1 => 'Редовни',
            2 => 'Ванредни',
            default => '',
        };
    }
}
