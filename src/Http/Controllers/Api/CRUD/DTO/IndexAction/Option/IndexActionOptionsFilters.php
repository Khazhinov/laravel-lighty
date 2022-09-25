<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Option;

use Khazhinov\PhpSupport\Enums\ScalarTypeEnum;
use Khazhinov\PhpSupport\DTO\DataTransferObject;
use Khazhinov\PhpSupport\DTO\Validation\ArrayOfScalar;

class IndexActionOptionsFilters extends DataTransferObject
{
    /**
     * @var bool
     */
    public bool $enable = true;

    /**
     * Массив столбцов, которые будут проигнорированы при построении фильтрующих условий
     *
     * @var array<string>
     */
    #[ArrayOfScalar(type: ScalarTypeEnum::String)]
    public array $ignore = [];
}
