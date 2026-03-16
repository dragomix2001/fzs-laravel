<?php

namespace App\Events;

use App\Models\PrijavaIspita;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IspitPrijavljen
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $prijava;

    public function __construct(PrijavaIspita $prijava)
    {
        $this->prijava = $prijava;
    }
}
