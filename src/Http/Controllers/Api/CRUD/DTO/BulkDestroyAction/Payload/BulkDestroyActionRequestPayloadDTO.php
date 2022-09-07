<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\BulkDestroyAction\Payload;

use Khazhinov\LaravelLighty\DTO\DataTransferObject;
use Khazhinov\LaravelLighty\DTO\Validation\ArrayOfScalar;
use Khazhinov\LaravelLighty\Enums\ScalarTypeEnum;

class BulkDestroyActionRequestPayloadDTO extends DataTransferObject
{
    /**
     * @var array<string>
     */
    #[ArrayOfScalar(ScalarTypeEnum::String)]
    public array $ids = [];
}
