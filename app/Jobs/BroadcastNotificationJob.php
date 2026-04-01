<?php

namespace App\Jobs;

use App\Models\Obavestenje;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class BroadcastNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public Obavestenje $obavestenje,
        public array $userIds
    ) {}

    public function handle(NotificationService $notificationService): void
    {
        foreach ($this->userIds as $userId) {
            $notificationService->notifyUser(
                $userId,
                'Ново обавештење',
                $this->obavestenje->naslov,
                'info',
                [
                    'id' => $this->obavestenje->id,
                    'link' => route('obavestenja.show', $this->obavestenje->id),
                ]
            );
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Слање обавештења није успело', [
            'obavestenje_id' => $this->obavestenje->id,
            'obavestenje_naslov' => $this->obavestenje->naslov,
            'user_ids_count' => count($this->userIds),
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
