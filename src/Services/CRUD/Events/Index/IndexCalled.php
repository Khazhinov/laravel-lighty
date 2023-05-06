<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Services\CRUD\Events\Index;

use Illuminate\Foundation\Events\Dispatchable;
use Khazhinov\LaravelLighty\Services\CRUD\Events\BaseCRUDEvent;

class IndexCalled extends BaseCRUDEvent
{
    use Dispatchable;
}
