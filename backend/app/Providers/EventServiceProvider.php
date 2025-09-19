<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\ConversionConfirmed;
use App\Events\TrialExpiring;
use App\Events\LeadCreated;
use App\Events\MessageReceived;
use App\Listeners\SendConversionToAdvertisingPlatforms;
use App\Listeners\NotifyTrialExpiring;
use App\Listeners\TriggerLeadWebhooks;
use App\Listeners\ProcessMessageForNLP;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        ConversionConfirmed::class => [
            SendConversionToAdvertisingPlatforms::class,
        ],

        TrialExpiring::class => [
            NotifyTrialExpiring::class,
        ],

        LeadCreated::class => [
            TriggerLeadWebhooks::class,
        ],

        MessageReceived::class => [
            ProcessMessageForNLP::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
