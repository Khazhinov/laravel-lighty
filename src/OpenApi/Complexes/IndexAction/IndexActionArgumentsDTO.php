<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\OpenApi\Complexes\IndexAction;

use Illuminate\Database\Eloquent\Model;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Option\IndexActionOptionsDTO;
use Khazhinov\LaravelLighty\Http\Resources\CollectionResource;
use Khazhinov\PhpSupport\DTO\DataTransferObject;
use Khazhinov\PhpSupport\DTO\Validation\ExistsInParents;

class IndexActionArgumentsDTO extends DataTransferObject
{
    public IndexActionOptionsDTO $options;

    #[ExistsInParents(parent: Model::class)]
    public string $model_class;

    #[ExistsInParents(parent: CollectionResource::class)]
    public string $collection_resource;
}
