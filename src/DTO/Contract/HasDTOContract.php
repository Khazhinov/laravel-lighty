<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\DTO\Contract;

use Khazhinov\LaravelLighty\DTO\DataTransferObject;

interface HasDTOContract
{
    public function getDTO(): DataTransferObject;
}
