<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | When disabled, calls to ->logQuery() are a no-op and simply return the
    | builder, so it is safe to leave them in code. Defaults to APP_DEBUG.
    |
    */

    'enabled' => env('QUERY_LOG_ENABLED', env('APP_DEBUG', false)),

    /*
    |--------------------------------------------------------------------------
    | Log channel
    |--------------------------------------------------------------------------
    |
    | The channel (from config/logging.php) where queries are written.
    | Null uses the application's default channel.
    |
    */

    'channel' => env('QUERY_LOG_CHANNEL'),

    /*
    |--------------------------------------------------------------------------
    | Log level
    |--------------------------------------------------------------------------
    |
    | Default PSR-3 level used when ->logQuery() does not receive one.
    |
    */

    'level' => env('QUERY_LOG_LEVEL', 'debug'),

    /*
    |--------------------------------------------------------------------------
    | Include caller
    |--------------------------------------------------------------------------
    |
    | Adds the file and line where ->logQuery() was called to the log context.
    |
    */

    'include_caller' => true,

];
