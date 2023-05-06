<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Services\CRUD\Events;

use Illuminate\Queue\SerializesModels;
use Spatie\DataTransferObject\DataTransferObject;
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
        if ($this->data instanceof DataTransferObject) {
            $this->data = $this->data->toArray();
        }

        event(new CRUDEvent(static::class, $this->modelClass, $this->data, $this->exception));
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
