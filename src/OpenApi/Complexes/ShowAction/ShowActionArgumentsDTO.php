<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\OpenApi\Complexes\ShowAction;

use Illuminate\Database\Eloquent\Model;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\ShowAction\Option\ShowActionOptionsDTO;
use Khazhinov\LaravelLighty\Http\Resources\SingleResource;
use Khazhinov\PhpSupport\DTO\DataTransferObject;
use Khazhinov\PhpSupport\DTO\Validation\ExistsInParents;

class ShowActionArgumentsDTO extends DataTransferObject
{
    public ShowActionOptionsDTO $options;

    #[ExistsInParents(parent: Model::class)]
    public string $model_class;

    #[ExistsInParents(parent: SingleResource::class)]
    public string $single_resource;
}
