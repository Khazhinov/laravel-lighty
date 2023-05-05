<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Services\CRUD\Exceptions;

use RuntimeException;

class UnsupportedModelException extends RuntimeException
{
    public function __construct(string $current_class, string $base_class)
    {
        parent::__construct("Class $current_class must be inherited from class $base_class", 400);
    }
}
