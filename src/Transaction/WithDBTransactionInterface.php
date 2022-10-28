<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Transaction;

use Closure;

interface WithDBTransactionInterface
{
    public function beginTransaction(): void;

    public function commit(): void;

    public function rollback(): void;

    public function transaction(Closure $closure): mixed;
}
