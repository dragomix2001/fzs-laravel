<?php

namespace App\Events;

use App\Models\Kandidat;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KandidatCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $kandidat;

    public function __construct(Kandidat $kandidat)
    {
        $this->kandidat = $kandidat;
    }
}
