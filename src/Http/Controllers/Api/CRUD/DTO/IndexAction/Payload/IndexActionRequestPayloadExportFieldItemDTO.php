<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Payload;

use Khazhinov\PhpSupport\DTO\DataTransferObject;

class IndexActionRequestPayloadExportFieldItemDTO extends DataTransferObject
{
    /**
     * @var string
     */
    public string $column;

    /**
     * @var string
     */
    public string $alias;
}
