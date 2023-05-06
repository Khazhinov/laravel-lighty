<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Services\CRUD\Events\BulkDestroy;

use Illuminate\Foundation\Events\Dispatchable;
use Khazhinov\LaravelLighty\Services\CRUD\Events\BaseCRUDEvent;

class BulkDestroyEnded extends BaseCRUDEvent
{
    use Dispatchable;
}
