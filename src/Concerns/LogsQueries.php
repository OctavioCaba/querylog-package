<?php

namespace Cabaoctavio\QueryLog\Concerns;

use Cabaoctavio\QueryLog\QueryLogger;
use Illuminate\Database\Eloquent\Builder;

/**
 * Adds a chainable ->logQuery() scope that logs the SQL built up to that point.
 *
 *     User::where('active', true)
 *         ->logQuery('after active')
 *         ->whereHas('posts')
 *         ->logQuery('after posts')
 *         ->get();
 */
trait LogsQueries
{
    /**
     * Log the query built so far and return the builder to keep chaining.
     */
    public function scopeLogQuery(Builder $query, ?string $label = null, ?string $level = null): Builder
    {
        app(QueryLogger::class)->log($query, $label, $level);

        return $query;
    }
}
