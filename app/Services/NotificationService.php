<?php

namespace App\Services;

use App\Events\NewNotification;
use App\Models\Obavestenje;
use App\Models\User;

class NotificationService
{
    public function notifyUser(int $userId, string $title, string $message, string $type = 'info', ?array $data = null): void
    {
        NewNotification::dispatch($userId, $title, $message, $type, $data);
    }

    public function notifyAdmins(string $title, string $message, string $type = 'info', ?array $data = null): void
    {
        $admins = User::where('role', User::ROLE_ADMIN)->get();

        foreach ($admins as $admin) {
            $this->notifyUser($admin->id, $title, $message, $type, $data);
        }
    }

    public function broadcastObavestenje(Obavestenje $obavestenje): void
    {
        $targetUsers = match ($obavestenje->tip) {
            'javno' => User::all(),
            'profesori' => User::where('role', User::ROLE_PROFESSOR)->get(),
            default => User::where('role', User::ROLE_ADMIN)->get(),
        };

        foreach ($targetUsers as $user) {
            $this->notifyUser(
                $user->id,
                'Ново обавештење',
                $obavestenje->naslov,
                'info',
                [
                    'id' => $obavestenje->id,
                    'link' => route('obavestenja.show', $obavestenje->id),
                ]
            );
        }
    }

    public function notifyExamDeadline(int $userId, string $examName, string $deadline): void
    {
        $this->notifyUser(
            $userId,
            'Рок за пријаву испита',
            "Пријава за {$examName} истиче {$deadline}",
            'warning'
        );
    }
}
