<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO;

use Khazhinov\LaravelLighty\DTO\DataTransferObject;
use Khazhinov\LaravelLighty\DTO\Validation\ClassExists;
use Khazhinov\LaravelLighty\DTO\Validation\ExistsInParents;
use ReflectionException;
use RuntimeException;

class ApiCRUDControllerActionInitDTO extends DataTransferObject
{
    public string $action_name;

    /**
     * @var array<mixed>
     */
    public array $action_options;

    #[ClassExists]
    #[ExistsInParents(parent: ApiCRUDControllerOptionDTO::class)]
    public string $action_option_class;

    /**
     * @param  ApiCRUDControllerMetaDTO  $controller_meta
     * @return ApiCRUDControllerOptionDTO
     */
    public function getActionOptionDTO(ApiCRUDControllerMetaDTO $controller_meta): ApiCRUDControllerOptionDTO
    {
        $relationships_ignore_allowed = false;
        if (! $controller_meta->hasAllowedRelationships()) {
            $relationships_ignore_allowed = true;
        }

        $base_options = [
            'relationships' => [
                'ignore_allowed' => $relationships_ignore_allowed,
            ],
        ];

        if ($this->action_options) {
            $action_option_dto = new $this->action_option_class(array_merge_recursive_distinct($base_options, $this->action_options));
        } else {
            $action_option_dto = new $this->action_option_class($base_options);
        }

        if (! is_a($action_option_dto, ApiCRUDControllerOptionDTO::class, true)) {
            $tmp_class = $action_option_dto;
            $tmp_base_class = ApiCRUDControllerOptionDTO::class;

            throw new RuntimeException("Class $tmp_class must be inherited from class $tmp_base_class");
        }

        return $action_option_dto;
    }
}
