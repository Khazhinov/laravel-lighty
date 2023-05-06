<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Services\CRUD\Events\Destroy;

use Illuminate\Foundation\Events\Dispatchable;
use Khazhinov\LaravelLighty\Services\CRUD\Events\BaseCRUDEvent;

class DestroyError extends BaseCRUDEvent
{
    use Dispatchable;
}
