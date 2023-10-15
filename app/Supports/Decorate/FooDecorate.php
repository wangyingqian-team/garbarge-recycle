<?php
namespace App\Supports\Decorate;

class FooDecorate
{
    public function decorate($data, \Closure $decorate)
    {
        $data['foo'] = 'foo';

        return $decorate($data);
    }
}
