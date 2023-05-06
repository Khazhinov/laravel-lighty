<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Services\CRUD\Exceptions;

use Throwable;
use RuntimeException;

class UndefinedCRUDEventException extends RuntimeException
{
    public function __construct(string $message = "Undefined CRUD event class", int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
