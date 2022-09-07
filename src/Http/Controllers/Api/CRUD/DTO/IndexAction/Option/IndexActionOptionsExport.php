<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Option;

use Khazhinov\LaravelLighty\DTO\DataTransferObject;
use Khazhinov\LaravelLighty\DTO\Validation\ClassExists;
use Khazhinov\LaravelLighty\Exports\ModelExport;

class IndexActionOptionsExport extends DataTransferObject
{
    /**
     * @var bool
     */
    public bool $enable = true;

    /**
     * @var string
     */
    #[ClassExists]
    public string $exporter_class = ModelExport::class;
}
