<?php

namespace Cabaoctavio\QueryLog\Tests\Feature;

use Cabaoctavio\QueryLog\Tests\Fixtures\User;
use Cabaoctavio\QueryLog\Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;

class LogsQueriesTest extends TestCase
{
    public function test_it_logs_the_query_built_so_far(): void
    {
        User::where('name', 'Octavio')->logQuery();

        $this->assertCount(1, $this->logged);
        $this->assertSame('debug', $this->logged[0]->level);
        $this->assertSame(
            '[query-log] select * from "users" where "name" = \'Octavio\' and "users"."deleted_at" is null',
            $this->logged[0]->message,
        );
    }

    public function test_it_logs_each_step_of_the_chain(): void
    {
        User::where('name', 'Octavio')
            ->logQuery('first')
            ->where('active', true)
            ->logQuery('second')
            ->get();

        $this->assertCount(2, $this->logged);
        $this->assertStringNotContainsString('"active"', $this->logged[0]->message);
        $this->assertStringStartsWith('[query-log] second:', $this->logged[1]->message);
        $this->assertStringContainsString('"active" = 1', $this->logged[1]->message);
    }

    public function test_it_returns_the_builder_and_does_not_alter_results(): void
    {
        User::create(['name' => 'Octavio']);
        User::create(['name' => 'Otro']);

        $query = User::where('name', 'Octavio')->logQuery();

        $this->assertInstanceOf(Builder::class, $query);
        $this->assertSame(['Octavio'], $query->pluck('name')->all());
    }

    public function test_it_includes_context(): void
    {
        User::where('name', 'Octavio')->logQuery('users');

        $context = $this->logged[0]->context;

        $this->assertSame('users', $context['label']);
        $this->assertSame(User::class, $context['model']);
        $this->assertSame('testing', $context['connection']);
        $this->assertSame('select * from "users" where "name" = ? and "users"."deleted_at" is null', $context['sql']);
        $this->assertSame(['Octavio'], $context['bindings']);
        $this->assertStringContainsString('LogsQueriesTest.php:', $context['caller']);
    }

    public function test_it_can_be_called_statically(): void
    {
        User::logQuery();

        $this->assertStringContainsString('select * from "users"', $this->logged[0]->message);
    }

    public function test_it_accepts_a_custom_level(): void
    {
        User::query()->logQuery(level: 'info');

        $this->assertSame('info', $this->logged[0]->level);
    }

    public function test_it_uses_the_configured_level(): void
    {
        config(['query-log.level' => 'warning']);

        User::query()->logQuery();

        $this->assertSame('warning', $this->logged[0]->level);
    }

    public function test_it_does_nothing_when_disabled(): void
    {
        config(['query-log.enabled' => false]);

        $query = User::where('name', 'Octavio')->logQuery();

        $this->assertCount(0, $this->logged);
        $this->assertInstanceOf(Builder::class, $query);
    }

    public function test_caller_can_be_excluded(): void
    {
        config(['query-log.include_caller' => false]);

        User::query()->logQuery();

        $this->assertArrayNotHasKey('caller', $this->logged[0]->context);
    }
}
