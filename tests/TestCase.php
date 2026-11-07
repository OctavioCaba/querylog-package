<?php

namespace Cabaoctavio\QueryLog\Tests;

use Cabaoctavio\QueryLog\QueryLogServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /** @var array<int, MessageLogged> */
    protected array $logged = [];

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        $this->logged = [];
        Event::listen(MessageLogged::class, fn (MessageLogged $event) => $this->logged[] = $event);
    }

    protected function getPackageProviders($app): array
    {
        return [QueryLogServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('logging.default', 'null');
        $app['config']->set('query-log.enabled', true);
    }
}
