<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Services\CRUD\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Throwable;

class CRUDEvent extends BaseCRUDEvent
{
    use Dispatchable;

    /**
     * @param  class-string  $eventClass
     * @param  class-string  $modelClass
     * @param  mixed  $data
     * @param  Throwable|null  $exception
     */
    public function __construct(
        public string $eventClass,
        string $modelClass,
        mixed $data,
        ?Throwable $exception = null
    ) {
        parent::__construct($modelClass, $data, $exception);
    }
}
