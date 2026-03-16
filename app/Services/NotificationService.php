<?php

namespace App\Services;

use App\Mail\IspitPrijavaMail;
use App\Mail\ObavestenjeMail;
use App\Models\Kandidat;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function sendObavestenjeToStudent(Kandidat $student, $naslov, $sadrzaj, $tip = 'opste')
    {
        if (! $student->email) {
            return false;
        }

        try {
            Mail::to($student->email)->send(new ObavestenjeMail($naslov, $sadrzaj, $tip));

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send obavestenje email: '.$e->getMessage());

            return false;
        }
    }

    public function sendObavestenjeToAllStudents($naslov, $sadrzaj, $tip = 'opste')
    {
        $students = Kandidat::whereNotNull('email')
            ->where('email', '!=', '')
            ->where('statusUpisa_id', 3)
            ->get();

        $sent = 0;
        foreach ($students as $student) {
            if ($this->sendObavestenjeToStudent($student, $naslov, $sadrzaj, $tip)) {
                $sent++;
            }
        }

        return $sent;
    }

    public function sendIspitPrijava(Kandidat $student, $predmet, $rok, $datum)
    {
        if (! $student->email) {
            return false;
        }

        try {
            Mail::to($student->email)->send(new IspitPrijavaMail(
                $student->imeKandidata.' '.$student->prezimeKandidata,
                $predmet,
                $rok,
                $datum
            ));

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send ispit prijava email: '.$e->getMessage());

            return false;
        }
    }
}
