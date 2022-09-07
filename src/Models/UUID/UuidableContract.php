<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Models\UUID;

interface UuidableContract
{
    public function generateUuid(): string;
}
