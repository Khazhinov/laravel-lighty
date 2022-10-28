<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\OpenApi\Complexes\SetPositionAction;

use Illuminate\Database\Eloquent\Model;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\SetPositionAction\Option\SetPositionActionOptionsDTO;
use Khazhinov\PhpSupport\DTO\DataTransferObject;
use Khazhinov\PhpSupport\DTO\Validation\ExistsInParents;

class SetPositionActionArgumentsDTO extends DataTransferObject
{
    public SetPositionActionOptionsDTO $options;

    #[ExistsInParents(parent: Model::class)]
    public string $model_class;
}
