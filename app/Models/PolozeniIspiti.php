<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolozeniIspiti extends Model
{
    use Auditable;

    protected $table = 'polozeni_ispiti';

    protected $fillable = [
        'prijava_id',
        'zapisnik_id',
        'kandidat_id',
        'predmet_id',
        'ocenaPismeni',
        'ocenaUsmeni',
        'konacnaOcena',
        'brojBodova',
        'statusIspita',
        'odluka_id',
        'indikatorAktivan',
    ];

    protected $casts = [
        'prijava_id' => 'integer',
        'zapisnik_id' => 'integer',
        'kandidat_id' => 'integer',
        'predmet_id' => 'integer',
        'ocenaPismeni' => 'integer',
        'ocenaUsmeni' => 'integer',
        'konacnaOcena' => 'integer',
        'brojBodova' => 'integer',
        'statusIspita' => 'integer',
        'odluka_id' => 'integer',
        'indikatorAktivan' => 'boolean',
    ];

    public function kandidat(): BelongsTo
    {
        return $this->belongsTo(Kandidat::class, 'kandidat_id');
    }

    public function predmet(): BelongsTo
    {
        return $this->belongsTo(PredmetProgram::class, 'predmet_id');
    }

    public function prijava(): BelongsTo
    {
        return $this->belongsTo(PrijavaIspita::class, 'prijava_id');
    }

    public function zapisnik(): BelongsTo
    {
        return $this->belongsTo(ZapisnikOPolaganjuIspita::class, 'zapisnik_id');
    }
}
