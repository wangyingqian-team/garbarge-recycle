<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{


    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //加载自定义函数
        $this->loadFunction();

    }

    protected function loadFunction()
    {
        foreach (glob(app_path('Supports/Function') . '/*.php') as $file) {
            require_once $file;
        }
    }

}
