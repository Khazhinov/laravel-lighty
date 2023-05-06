<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Services\CRUD\Events;

use Illuminate\Queue\SerializesModels;
use Throwable;

abstract class BaseCRUDEvent
{
    use SerializesModels;

    /**
     * @param  class-string  $modelClass
     * @param  mixed  $data
     * @param  Throwable|null  $exception
     */
    public function __construct(
        public string $modelClass,
        public mixed $data,
        public ?Throwable $exception = null,
    ) {
    }

    /**
     * @return string
     */
    public function getModelClass(): string
    {
        return $this->modelClass;
    }

    /**
     * @return mixed
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * @return Throwable|null
     */
    public function getException(): ?Throwable
    {
        return $this->exception;
    }

    public function hasException(): bool
    {
        return ! is_null($this->exception);
    }
}
