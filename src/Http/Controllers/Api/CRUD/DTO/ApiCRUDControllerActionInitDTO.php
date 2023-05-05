<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO;

use Khazhinov\PhpSupport\DTO\DataTransferObject;
use Khazhinov\PhpSupport\DTO\Validation\ClassExists;
use Khazhinov\PhpSupport\DTO\Validation\ExistsInParents;
use RuntimeException;

class ApiCRUDControllerActionInitDTO extends DataTransferObject
{
    public string $action_name;

    /**
     * @var array<mixed>
     */
    public array $action_options;

    #[ClassExists]
    #[ExistsInParents(parent: BaseCRUDOptionDTO::class)]
    public string $action_option_class;

    /**
     * @param  ApiCRUDControllerMetaDTO  $controller_meta
     * @return BaseCRUDOptionDTO
     */
    public function getActionOptionDTO(ApiCRUDControllerMetaDTO $controller_meta): BaseCRUDOptionDTO
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
            $action_option_dto = new $this->action_option_class(helper_array_merge_recursive_distinct($base_options, $this->action_options));
        } else {
            $action_option_dto = new $this->action_option_class($base_options);
        }

        if (! is_a($action_option_dto, BaseCRUDOptionDTO::class, true)) {
            /** @var string $tmp_class */
            $tmp_class = $action_option_dto;
            $tmp_base_class = BaseCRUDOptionDTO::class;

            throw new RuntimeException(sprintf("Class %s must be inherited from class %s", $tmp_class, $tmp_base_class));
        }

        return $action_option_dto;
    }
}
