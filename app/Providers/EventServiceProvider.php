<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\UserActivityEvent;
use App\Listeners\LogUserActivity;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * This ensures each event triggers its listener exactly once.
     */
    protected $listen = [
        UserActivityEvent::class => [
            LogUserActivity::class,
        ],
    ];

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Nothing else here, do NOT manually register listeners
    }
}
