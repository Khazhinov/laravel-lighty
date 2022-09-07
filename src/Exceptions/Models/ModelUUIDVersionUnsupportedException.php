<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Exceptions\Models;

use Khazhinov\LaravelLighty\Exceptions\Exception;
use Throwable;

class ModelUUIDVersionUnsupportedException extends Exception
{
    public function __construct(int $uuid_version, int $code = 400, Throwable $previous = null)
    {
        $message = "Unsupported UUID version: {$uuid_version}";

        parent::__construct($message, $code, $previous);
    }
}
