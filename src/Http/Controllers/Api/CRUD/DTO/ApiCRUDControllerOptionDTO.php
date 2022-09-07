<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO;

use Khazhinov\LaravelLighty\DTO\Custer\DataTransferObjectCaster;
use Khazhinov\LaravelLighty\DTO\DataTransferObject;
use Spatie\DataTransferObject\Attributes\CastWith;

abstract class ApiCRUDControllerOptionDTO extends DataTransferObject
{
    /**
     * @var ActionOptionsRelationships
     */
    #[CastWith(DataTransferObjectCaster::class, dto_class: ActionOptionsRelationships::class)]
    public ActionOptionsRelationships $relationships;

    /**
     * @var ActionOptionsDeleted
     */
    #[CastWith(DataTransferObjectCaster::class, dto_class: ActionOptionsDeleted::class)]
    public ActionOptionsDeleted $deleted;
}
