<?php

namespace App\Providers;

use App\Events\GarbageRecycleOrderCancelEvent;
use App\Events\GarbageRecycleOrderCreateEvent;
use App\Events\GarbageThrowOrderCancelEvent;
use App\Events\GarbageThrowOrderCreateEvent;
use App\Events\JifenOrderCreateEvent;
use App\Events\UserRegisterEvent;

use App\Listeners\GarbageRecycleOrderListener;
use App\Listeners\GarbageThrowOrderListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        JifenOrderCreateEvent::class => [],
        UserRegisterEvent::class => [],
        GarbageThrowOrderCreateEvent::class => [
            GarbageThrowOrderListener::class . '@createThrowOrder'
        ],
        GarbageThrowOrderCancelEvent::class => [
            GarbageThrowOrderListener::class . '@cancelThrowOrder'
        ],
        GarbageRecycleOrderCreateEvent::class => [
            GarbageRecycleOrderListener::class . '@createRecycleOrder'
        ],
        GarbageRecycleOrderCancelEvent::class => [
            GarbageRecycleOrderListener::class . '@cancelRecycleOrder'
        ]
    ];

}
