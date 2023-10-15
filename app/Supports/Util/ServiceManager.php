<?php
namespace App\Supports\Util;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Arr;

/**
 * 服务管理者
 *
 * Class ServiceManager
 *
 * @package App\Supports\Util
 */
class ServiceManager
{
    protected $app;

    protected $decorateStack = [];

    protected $dtos = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * 装饰栈
     *
     * @param $decorates
     */
   public function decorateStack($decorates)
   {
       array_unshift($this->decorateStack, Arr::wrap($decorates));
   }

    /**
     * 装饰数据
     *
     * @param $data
     * @param $decorates
     *
     * @return mixed
     */
   public function decorate($data)
   {
       if (!empty($this->decorateStack)){
           return (new Pipeline($this->app))
               ->send($data)
               ->through(array_shift($this->decorateStack))
               ->via('decorate')
               ->then(function ($data){
                   return $data;
               });
       }

       return $data;
   }

   public function getDto($dao)
   {
       if (!isset($this->daos[$dao])){
           $this->dtos[$dao] = new $dao;
       }

       return $this->dtos[$dao];
   }
}
