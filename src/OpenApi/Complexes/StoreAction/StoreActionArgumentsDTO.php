<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\OpenApi\Complexes\StoreAction;

use Illuminate\Database\Eloquent\Model;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\StoreAction\Option\StoreActionOptionsDTO;
use Khazhinov\LaravelLighty\Http\Requests\BaseRequest;
use Khazhinov\LaravelLighty\Http\Resources\SingleResource;
use Khazhinov\PhpSupport\DTO\DataTransferObject;
use Khazhinov\PhpSupport\DTO\Validation\ExistsInParents;

class StoreActionArgumentsDTO extends DataTransferObject
{
    public StoreActionOptionsDTO $options;

    #[ExistsInParents(parent: Model::class)]
    public string $model_class;

    #[ExistsInParents(parent: SingleResource::class)]
    public string $single_resource;

    #[ExistsInParents(parent: BaseRequest::class, nullable: true)]
    public ?string $validation_request = null;
}
