<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        'App\Events\KandidatCreated' => [
            'App\Listeners\SendKandidatCreatedNotification',
        ],
    ];

    /**
     * Register any other events for your application.
     */
    public function boot(): void
    {
        parent::boot();

        //
    }
}
