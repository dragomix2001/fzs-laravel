<?php

namespace App\Providers;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\KandidatCreated' => [
            'App\Listeners\SendKandidatCreatedNotification',
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @param  Dispatcher  $events
     */
    public function boot(): void
    {
        parent::boot();

        //
    }
}
