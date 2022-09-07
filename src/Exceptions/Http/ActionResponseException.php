<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Exceptions\Http;

use Khazhinov\LaravelLighty\Exceptions\Exception;
use Throwable;

class ActionResponseException extends Exception
{
    /**
     * @param  string  $message
     * @param  int  $code
     * @param  Throwable|null  $previous
     */
    public function __construct(string $message, int $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
