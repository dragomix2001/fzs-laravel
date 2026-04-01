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
        $adminIds = User::where('role', User::ROLE_ADMIN)->pluck('id');

        foreach ($adminIds as $adminId) {
            $this->notifyUser($adminId, $title, $message, $type, $data);
        }
    }

    public function broadcastObavestenje(Obavestenje $obavestenje): void
    {
        $targetUserIds = match ($obavestenje->tip) {
            'javno' => User::pluck('id'),
            'profesori' => User::where('role', User::ROLE_PROFESSOR)->pluck('id'),
            default => User::where('role', User::ROLE_ADMIN)->pluck('id'),
        };

        foreach ($targetUserIds as $userId) {
            $this->notifyUser(
                $userId,
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
