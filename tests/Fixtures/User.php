<?php

namespace Cabaoctavio\QueryLog\Tests\Fixtures;

use Cabaoctavio\QueryLog\Concerns\LogsQueries;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use LogsQueries;
    use SoftDeletes;

    protected $guarded = [];
}
