<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Transaction;

use Closure;
use Illuminate\Support\Facades\DB;
use Throwable;

trait WithDBTransaction
{
    public function beginTransaction(): void
    {
        DB::beginTransaction();
    }

    public function commit(): void
    {
        DB::commit();
    }

    public function rollback(): void
    {
        DB::rollback();
    }

    /**
     * @throws Throwable
     */
    public function transaction(Closure $closure): mixed
    {
        $this->beginTransaction();

        try {
            $result = $closure();

            $this->commit();

            return $result;
        } catch (Throwable $exception) {
            $this->rollback();

            throw new $exception();
        }
    }
}
