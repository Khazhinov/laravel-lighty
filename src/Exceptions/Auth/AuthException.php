<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Exceptions\Auth;

use Khazhinov\LaravelLighty\Exceptions\Exception;
use Throwable;

class AuthException extends Exception
{
    public function __construct(string $message, int $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
