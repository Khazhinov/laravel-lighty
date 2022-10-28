<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\OpenApi\Complexes\BulkDestroyAction;

use Illuminate\Database\Eloquent\Model;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\BulkDestroyAction\Option\BulkDestroyActionOptionsDTO;
use Khazhinov\PhpSupport\DTO\DataTransferObject;
use Khazhinov\PhpSupport\DTO\Validation\ExistsInParents;

class BulkDestroyActionArgumentsDTO extends DataTransferObject
{
    public BulkDestroyActionOptionsDTO $options;

    #[ExistsInParents(parent: Model::class)]
    public string $model_class;
}
