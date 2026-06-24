<?php

namespace App\Listeners;

use App\Events\KandidatCreated;
use App\Mail\KandidatCreatedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendKandidatCreatedNotification implements ShouldQueue
{
    public function handle(KandidatCreated $event): void
    {
        Mail::to($event->kandidat->email)->queue(new KandidatCreatedMail($event->kandidat));
    }
}
