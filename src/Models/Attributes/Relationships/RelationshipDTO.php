<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Models\Attributes\Relationships;

use Illuminate\Database\Eloquent\Model;
use Khazhinov\LaravelLighty\DTO\DataTransferObject;
use Khazhinov\LaravelLighty\DTO\Validation\ArrayOfScalar;
use Khazhinov\LaravelLighty\DTO\Validation\ClassExists;
use Khazhinov\LaravelLighty\DTO\Validation\ExistsInParents;
use Khazhinov\LaravelLighty\Enums\ScalarTypeEnum;

class RelationshipDTO extends DataTransferObject
{
    /**
     * @var string
     */
    #[ClassExists]
    #[ExistsInParents(Model::class)]
    public string $related;

    /**
     * @var RelationshipTypeEnum
     */
    public RelationshipTypeEnum $type;

    /**
     * @var array<string>
     */
    #[ArrayOfScalar(ScalarTypeEnum::String, true)]
    public array $aliases = [];
}
