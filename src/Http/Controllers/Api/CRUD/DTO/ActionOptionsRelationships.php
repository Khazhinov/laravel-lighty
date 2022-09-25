<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO;

use Khazhinov\PhpSupport\DTO\DataTransferObject;

class ActionOptionsRelationships extends DataTransferObject
{
    /**
     * @var bool
     */
    public bool $enable = true;

    /**
     * @var bool
     */
    public bool $ignore_allowed = false;
}
