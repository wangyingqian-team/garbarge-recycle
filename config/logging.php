<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'error_log'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [

        'slow_query' => [
            'driver' => 'single',
            'path' => storage_path('logs/slow_query.log'),
            'level' => 'debug',
            'days' => 14,
        ],

        'error_log' => [
            'driver' => 'single',
            'path' => storage_path('logs/error_log.log'),
            'level' => 'warn',
            'days' => 14,
        ],
        'default' => [
            'driver' => 'single',
            'path' => storage_path('logs/default.log'),
            'level' => 'info',
            'days' => 7,
        ],
        'wechat' => [
            'driver' => 'single',
            'path' => storage_path('logs/wechat.log'),
            'level' => 'info',
            'days' => 7,
        ],
        'user' => [
            'driver' => 'single',
            'path' => storage_path('logs/user.log'),
            'level' => 'info',
            'days' => 7,
        ],
        'throw' => [
            'driver' => 'single',
            'path' => storage_path('logs/throw.log'),
            'level' => 'info',
            'days' => 7,
        ],
        'recycle' => [
            'driver' => 'single',
            'path' => storage_path('logs/recycle.log'),
            'level' => 'info',
            'days' => 7,
        ],
    ],

];
