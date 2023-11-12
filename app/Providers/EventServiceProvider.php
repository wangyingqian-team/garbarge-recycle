<?php

namespace App\Providers;

use App\Events\GarbageRecycleOrderCancelEvent;
use App\Events\GarbageRecycleOrderCreateEvent;
use App\Events\GarbageRecycleOrderFinishEvent;
use App\Events\JifenOrderCreateEvent;
use App\Events\UserRegisterEvent;

use App\Listeners\GarbageRecycleOrderListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        JifenOrderCreateEvent::class => [],
        UserRegisterEvent::class => [],
        GarbageRecycleOrderCreateEvent::class => [
            GarbageRecycleOrderListener::class . '@createRecycleOrder'
        ],
        GarbageRecycleOrderCancelEvent::class => [
            GarbageRecycleOrderListener::class . '@cancelRecycleOrder'
        ],
        GarbageRecycleOrderFinishEvent::class => [
            GarbageRecycleOrderListener::class  . '@finishRecycleOrder'
        ]
    ];

}
