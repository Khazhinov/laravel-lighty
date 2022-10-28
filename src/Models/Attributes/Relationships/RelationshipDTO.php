<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Models\Attributes\Relationships;

use Illuminate\Database\Eloquent\Model;
use Khazhinov\PhpSupport\DTO\DataTransferObject;
use Khazhinov\PhpSupport\DTO\Validation\ArrayOfScalar;
use Khazhinov\PhpSupport\DTO\Validation\ClassExists;
use Khazhinov\PhpSupport\DTO\Validation\ExistsInParents;
use Khazhinov\PhpSupport\Enums\ScalarTypeEnum;

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
