<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Services\CRUD\Exceptions;

use RuntimeException;

class ColumnMustBeSpecifiedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Column must be specified', 400);
    }
}
