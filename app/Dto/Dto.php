<?php
namespace App\Dto;

use Illuminate\Database\Eloquent\Builder;


class Dto {

    /** @var Builder */
    public $query;


    public function __construct()
    {
        $model = 'App\\Models\\'.substr(static::class,8,-3).'Model';
        $this->query = $model::query();
    }
}
