<?php

namespace App\Mail;

use App\Models\Kandidat;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class KandidatCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Kandidat $kandidat)
    {
    }

    public function build()
    {
        return $this->subject('Добродошли - Факултет за спорт')
            ->view('emails.kandidat_created');
    }
}
