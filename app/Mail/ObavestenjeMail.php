<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ObavestenjeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $naslov;
    public $sadrzaj;
    public $tip;

    public function __construct($naslov, $sadrzaj, $tip = 'opste')
    {
        $this->naslov = $naslov;
        $this->sadrzaj = $sadrzaj;
        $this->tip = $tip;
    }

    public function build()
    {
        return $this->subject('Ново обавештење - ' . $this->naslov)
            ->view('emails.obavestenje');
    }
}
