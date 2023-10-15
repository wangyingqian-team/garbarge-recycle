<?php
namespace App\Providers;

use App\Supports\Macro\Builder;
use App\Supports\Macro\MacroInterface;
use App\Supports\Macro\Response;
use Illuminate\Support\ServiceProvider;

class MacroServiceProvider extends ServiceProvider
{
    protected $macros = [
        Builder::class,
        Response::class
    ];

    public function boot()
    {
        foreach ($this->macros as $macro){
            if (is_subclass_of($macro, MacroInterface::class)) {
                $this->app->call($macro . '@extend');
            }
        }
    }
}
