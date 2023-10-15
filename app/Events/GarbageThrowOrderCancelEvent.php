<?php

namespace App\Events;

class GarbageThrowOrderCancelEvent
{
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
}
