<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Services\CRUD\DTO;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\ActionClosureModeEnum;
use Khazhinov\LaravelLighty\Models\Model;
use Khazhinov\PhpSupport\DTO\Custer\EnumCaster;
use Khazhinov\PhpSupport\DTO\DataTransferObject;
use Spatie\DataTransferObject\Attributes\CastWith;
use Throwable;

class ActionClosureDataDTO extends DataTransferObject
{
    #[CastWith(EnumCaster::class, enumType: ActionClosureModeEnum::class)]
    public ActionClosureModeEnum $mode;

    /**
     * @var Builder|DatabaseBuilder|Builder[]|Collection|\Illuminate\Support\Collection|array<mixed>|Model|\Khazhinov\LaravelLightyMongoDBBundle\Models\Model
     */
    public mixed $data;

    public ?Throwable $exception = null;

    public function hasException(): bool
    {
        return ! is_null($this->exception);
    }
}
