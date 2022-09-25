<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\BulkDestroyAction\Payload;

use Khazhinov\PhpSupport\Enums\ScalarTypeEnum;
use Khazhinov\PhpSupport\DTO\DataTransferObject;
use Khazhinov\PhpSupport\DTO\Validation\ArrayOfScalar;

class BulkDestroyActionRequestPayloadDTO extends DataTransferObject
{
    /**
     * @var array<string>
     */
    #[ArrayOfScalar(ScalarTypeEnum::String)]
    public array $ids = [];
}
