<?php

namespace App\Events;

class GarbageRecycleOrderCancelEvent
{
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
}