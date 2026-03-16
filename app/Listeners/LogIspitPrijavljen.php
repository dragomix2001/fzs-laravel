<?php

namespace App\Listeners;

use App\Events\IspitPrijavljen;

class LogIspitPrijavljen
{
    public function __construct() {}

    public function handle(IspitPrijavljen $event)
    {
        \Log::info('Ispit prijavljen', [
            'kandidat_id' => $event->prijava->kandidat_id,
            'predmet_id' => $event->prijava->predmet_id,
            'rok_id' => $event->prijava->rok_id,
        ]);
    }
}
