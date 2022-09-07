<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO;

use Khazhinov\LaravelLighty\DTO\Custer\EnumCaster;
use Khazhinov\LaravelLighty\DTO\DataTransferObject;
use Spatie\DataTransferObject\Attributes\CastWith;

class ActionOptionsDeleted extends DataTransferObject
{
    /**
     * Требуется ли внедрять в Query Builder фильтрацию с учётом Soft Delete
     *
     * @var bool
     */
    public bool $enable = true;

    /**
     * Поле для проверки SoftDelete
     *
     * @var string
     */
    public string $column = 'deleted_at';

    /**
     * Режим загрузки удаленных сущностей
     *
     * @var ActionOptionsDeletedModeEnum
     */
    #[CastWith(EnumCaster::class, enumType: ActionOptionsDeletedModeEnum::class)]
    public ActionOptionsDeletedModeEnum $mode = ActionOptionsDeletedModeEnum::WithoutTrashed;
}
