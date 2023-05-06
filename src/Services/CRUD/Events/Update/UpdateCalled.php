<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Services\CRUD\Events\Update;

use Illuminate\Foundation\Events\Dispatchable;
use Khazhinov\LaravelLighty\Services\CRUD\Events\BaseCRUDEvent;

class UpdateCalled extends BaseCRUDEvent
{
    use Dispatchable;
}
