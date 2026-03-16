<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class IspitPrijjavaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $student;

    public $predmet;

    public $rok;

    public $datum;

    public function __construct($student, $predmet, $rok, $datum)
    {
        $this->student = $student;
        $this->predmet = $predmet;
        $this->rok = $rok;
        $this->datum = $datum;
    }

    public function build()
    {
        return $this->subject('Пријава испита - '.$this->predmet)
            ->view('emails.ispit_prijava');
    }
}
