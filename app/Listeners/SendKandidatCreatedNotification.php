<?php

namespace App\Listeners;

use App\Events\KandidatCreated;
use App\Mail\KandidatCreatedMail;
use Illuminate\Support\Facades\Mail;

class SendKandidatCreatedNotification
{
    public function handle(KandidatCreated $event): void
    {
        Mail::to($event->kandidat->email)->send(new KandidatCreatedMail($event->kandidat));
    }
}
