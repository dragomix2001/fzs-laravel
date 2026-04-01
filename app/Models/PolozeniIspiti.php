<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolozeniIspiti extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $table = 'polozeni_ispiti';

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
