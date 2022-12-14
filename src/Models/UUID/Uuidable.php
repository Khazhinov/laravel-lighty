<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Models\UUID;

use Khazhinov\LaravelLighty\Exceptions\Models\ModelUUIDVersionUnsupportedException;
use Ramsey\Uuid\Uuid;

trait Uuidable
{
    /**
     * @throws ModelUUIDVersionUnsupportedException
     * @return string
     */
    public function generateUuid(): string
    {
        return match (config('lighty.models.uuid.version')) {
            4 => Uuid::uuid4()->toString(),
            default => throw new ModelUUIDVersionUnsupportedException(config('lighty.models.uuid.version')),
        };
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
     *
     * @return bool
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * Get the auto-incrementing key type.
     *
     * @return string
     */
    public function getKeyType(): string
    {
        return 'string';
    }
}
