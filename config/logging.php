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

    'default' => env('LOG_CHANNEL', 'stack'),

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
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => env('LOG_LEVEL', 'critical'),
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => SyslogUdpHandler::class,
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

        'order_callback' => [
            'driver' => 'daily',
            'path' => storage_path('logs/order_callback.log'),
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],
        //充值回调日志
        'recharge_callback' => [
            'driver' => 'daily',
            'path' => storage_path('logs/recharge_callback.log'),
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],
        'recharge' => [
            'driver' => 'daily',
            'path' => storage_path('logs/recharge.log'),
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],
        'withdraw' => [
            'driver' => 'daily',
            'path' => storage_path('logs/withdraw.log'),
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],
        'withdraw_callback' => [
            'driver' => 'daily',
            'path' => storage_path('logs/withdraw_callback.log'),
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],

        'console' => [
            'driver' => 'daily',
            'path' => storage_path('logs/console.log'),
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],

        'task' => [
            'driver' => 'daily',
            'path' => storage_path('logs/task.log'),
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],
        'sync_price' => [
            'driver' => 'daily',
            'path' => storage_path('logs/sync_price.log'),
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],
        
        'gateway' => [
            'driver' => 'daily',
            'path' => storage_path('logs/gateway.log'),
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],
        'lp_info' => [
            'driver' => 'daily',
            'path' => storage_path('logs/lp_info.log'),
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],
        'ths_order' => [
            'driver' => 'daily',
            'path' => storage_path('logs/ths_order.log'),
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],
        'sync_address_balance' => [
            'driver' => 'daily',
            'path' => storage_path('logs/sync_address_balance.log'),
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],
        'auto_trade_detail' => [
            'driver' => 'daily',
            'path' => storage_path('logs/auto_trade_detail.log'),
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],
        'buy_mine' => [
            'driver' => 'daily',
            'path' => storage_path('logs/buy_mine.log'),
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],
        'buy_ticket' => [
            'driver' => 'daily',
            'path' => storage_path('logs/buy_ticket.log'),
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],
        'xhy_swap' => [
            'driver' => 'daily',
            'path' => storage_path('logs/xhy_swap.log'),
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],
        'signLog' => [
            'driver' => 'daily',
            'path' => storage_path('logs/signLog.log'),
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],
        'team_lottery' => [
            'driver' => 'daily',
            'path' => storage_path('logs/team_lottery.log'),
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],
        'team_withdraw' => [
            'driver' => 'daily',
            'path' => storage_path('logs/team_withdraw.log'),
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],
        'team_withdraw' => [
            'driver' => 'daily',
            'path' => storage_path('logs/team_withdraw.log'),
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],
        'swap_getprice' => [
            'driver' => 'daily',
            'path' => storage_path('logs/swap_getprice.log'),
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],
        'swap_purchase' => [
            'driver' => 'daily',
            'path' => storage_path('logs/swap_purchase.log'),
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],
    ],

];
