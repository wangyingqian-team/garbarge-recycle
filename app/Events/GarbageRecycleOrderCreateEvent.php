<?php

namespace App\Events;

class GarbageRecycleOrderCreateEvent
{
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
}