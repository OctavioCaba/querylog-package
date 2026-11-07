<?php

namespace Cabaoctavio\QueryLog;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Log\LogManager;

class QueryLogger
{
    public function __construct(
        protected Config $config,
        protected LogManager $log,
    ) {
    }

    /**
     * Write the SQL built so far by the given builder to the log.
     */
    public function log(EloquentBuilder|QueryBuilder $query, ?string $label = null, ?string $level = null): void
    {
        if (! $this->config->get('query-log.enabled')) {
            return;
        }

        // toBase() applies global scopes on a clone, so the logged SQL matches
        // what would actually run, without mutating the original builder.
        $base = $query instanceof EloquentBuilder ? $query->toBase() : $query;

        $raw = $base->toRawSql();

        $context = array_filter([
            'label' => $label,
            'model' => $query instanceof EloquentBuilder ? $query->getModel()::class : null,
            'connection' => $base->getConnection()->getName(),
            'sql' => $base->toSql(),
            'bindings' => $base->getBindings(),
            'caller' => $this->config->get('query-log.include_caller') ? $this->caller() : null,
        ], fn ($value) => $value !== null);

        $message = $label === null ? "[query-log] {$raw}" : "[query-log] {$label}: {$raw}";

        $this->log
            ->channel($this->config->get('query-log.channel'))
            ->log($level ?? $this->config->get('query-log.level', 'debug'), $message, $context);
    }

    /**
     * Find the first frame outside this package and the vendor directory.
     */
    protected function caller(): ?string
    {
        $packagePath = $this->normalizePath(__DIR__);

        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $frame) {
            if (! isset($frame['file'])) {
                continue;
            }

            $file = $this->normalizePath($frame['file']);

            if (str_starts_with($file, $packagePath) || str_contains($file, '/vendor/')) {
                continue;
            }

            return $frame['file'].':'.($frame['line'] ?? 0);
        }

        return null;
    }

    protected function normalizePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }
}
