<?php

namespace Cabaoctavio\QueryLog;

use Illuminate\Support\ServiceProvider;

class QueryLogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/query-log.php', 'query-log');

        $this->app->singleton(QueryLogger::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/query-log.php' => config_path('query-log.php'),
            ], 'query-log-config');
        }
    }
}
