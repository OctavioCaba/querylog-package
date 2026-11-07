# Laravel Query Log

An Eloquent model trait that logs the SQL query **built so far**, at any point of the builder chain, with bindings already interpolated.

```php
User::where('active', true)
    ->logQuery('after active')
    ->whereHas('posts')
    ->logQuery('after posts')
    ->get();
```

```
local.DEBUG: [query-log] after active: select * from "users" where "active" = 1 {"label":"after active","model":"App\\Models\\User","connection":"mysql","sql":"select * from \"users\" where \"active\" = ?","bindings":[true],"caller":"/app/Http/Controllers/UserController.php:21"}
local.DEBUG: [query-log] after posts: select * from "users" where "active" = 1 and exists (select * from "posts" where ...) {...}
```

## Requirements

- PHP 8.1+
- Laravel 10.15+, 11, 12 or 13

## Installation

```bash
composer require cabaoctavio/laravel-query-log --dev
```

The service provider is auto-discovered. Optionally publish the config:

```bash
php artisan vendor:publish --tag=query-log-config
```

## Usage

Add the trait to any model:

```php
use Cabaoctavio\QueryLog\Concerns\LogsQueries;

class User extends Model
{
    use LogsQueries;
}
```

Then call `logQuery()` anywhere in the chain. It returns the same builder, so you can keep chaining:

```php
User::logQuery();                                   // static call
User::where('name', 'Octavio')->logQuery();         // no label
User::where('name', 'Octavio')->logQuery('search'); // with label
User::query()->logQuery(level: 'info');             // custom level
$user->posts()->logQuery()->latest()->get();        // through relations (related model must use the trait)
```

The logged SQL includes the model's global scopes (e.g. `SoftDeletes`), so it matches what will really run. `logQuery()` never alters the builder or the results.

## Configuration

| Key              | Env                 | Default     | Description                                         |
|------------------|---------------------|-------------|-----------------------------------------------------|
| `enabled`        | `QUERY_LOG_ENABLED` | `APP_DEBUG` | When `false`, `logQuery()` is a no-op.              |
| `channel`        | `QUERY_LOG_CHANNEL` | `null`      | Log channel to write to (`null` = default channel). |
| `level`          | `QUERY_LOG_LEVEL`   | `debug`     | Default log level.                                  |
| `include_caller` | —                   | `true`      | Adds the `file:line` where `logQuery()` was called. |

Because it defaults to `APP_DEBUG`, leftover `logQuery()` calls stay silent in production.

## Testing

```bash
composer test
```

## License

MIT. See [LICENSE.md](LICENSE.md).
