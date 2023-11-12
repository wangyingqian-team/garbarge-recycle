<?php
namespace App\Events;

class GarbageRecycleOrderFinishEvent
{
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
}
