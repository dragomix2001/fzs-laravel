<?php

namespace App;

class Skolarina extends AndroModel
{
    protected $table = 'skolarina';

    protected $casts = [
        'datum' => 'datetime',
    ];

    protected $guarded = [];

    public function kandidat()
    {
        return $this->belongsTo(Kandidat::class, 'kandidat_id');
    }

    public function uplate()
    {
        return $this->hasMany(UplataSkolarine::class, 'skolarina_id');
    }

    public function tipStudija()
    {
        return $this->belongsTo(TipStudija::class, 'tipStudija_id');
    }

    public function godinaStudija()
    {
        return $this->belongsTo(GodinaStudija::class, 'godinaStudija_id');
    }
}
