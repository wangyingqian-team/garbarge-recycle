<?php

namespace App\Events;

class GarbageThrowOrderCreateEvent
{
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
}