<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO;

use Khazhinov\LaravelLighty\DTO\DataTransferObject;
use Khazhinov\LaravelLighty\DTO\Validation\ArrayOfScalar;
use Khazhinov\LaravelLighty\DTO\Validation\ClassExists;
use Khazhinov\LaravelLighty\DTO\Validation\ExistsInParents;
use Khazhinov\LaravelLighty\Enums\ScalarTypeEnum;
use Khazhinov\LaravelLighty\Models\Model;

/**
 * Класс, описывающий необходимую конфигурацию CRUD абстракции
 */
class ApiCRUDControllerMetaDTO extends DataTransferObject
{
    #[ClassExists]
    #[ExistsInParents(parent: Model::class)]
    public string $model_class;

    #[ClassExists]
    public string $single_resource_class;

    #[ClassExists]
    public string $collection_resource_class;

    /**
     * @var array<string>
     */
    #[ArrayOfScalar(type: ScalarTypeEnum::String)]
    public array $allowed_relationships = [];

    public function hasAllowedRelationships(): bool
    {
        return (bool) count($this->allowed_relationships);
    }
}
