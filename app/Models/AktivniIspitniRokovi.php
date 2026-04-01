<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AktivniIspitniRokovi extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

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

    public static function tipRoka($id)
    {
        switch ($id) {
            case 1: return 'Редовни';
            case 2: return 'Ванредни';
        }

        return '';
    }
}
