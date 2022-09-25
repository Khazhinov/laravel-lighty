<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Option;

use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\ApiCRUDControllerOptionDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Payload\IndexActionRequestPayloadDTO;
use Khazhinov\LaravelLighty\Http\Resources\SingleResource;
use Khazhinov\PhpSupport\DTO\Custer\DataTransferObjectCaster;
use Khazhinov\PhpSupport\DTO\Validation\ClassExists;
use Khazhinov\PhpSupport\DTO\Validation\ExistsInParents;
use Spatie\DataTransferObject\Attributes\CastWith;

class IndexActionOptionsDTO extends ApiCRUDControllerOptionDTO
{
    /**
     * @var IndexActionOptionsFilters
     */
    #[CastWith(DataTransferObjectCaster::class, dto_class: IndexActionOptionsFilters::class)]
    public IndexActionOptionsFilters $filters;

    /**
     * @var IndexActionOptionsOrders
     */
    #[CastWith(DataTransferObjectCaster::class, dto_class: IndexActionOptionsOrders::class)]
    public IndexActionOptionsOrders $orders;

    /**
     * @var IndexActionOptionsPagination
     */
    #[CastWith(DataTransferObjectCaster::class, dto_class: IndexActionOptionsPagination::class)]
    public IndexActionOptionsPagination $pagination;

    /**
     * @var IndexActionOptionsExport
     */
    #[CastWith(DataTransferObjectCaster::class, dto_class: IndexActionOptionsExport::class)]
    public IndexActionOptionsExport $export;

    #[ClassExists(nullable: true)]
    #[ExistsInParents(parent: SingleResource::class, nullable: true)]
    public string|null $single_resource_class = null;

    /**
     * @param  IndexActionRequestPayloadDTO  $request
     * @return IndexActionOptionsReturnTypeEnum
     */
    public function getReturnTypeByRequestPayload(IndexActionRequestPayloadDTO $request): IndexActionOptionsReturnTypeEnum
    {
        if (! $this->export->enable) {
            return IndexActionOptionsReturnTypeEnum::Resource;
        }

        if ($request->hasExportColumns()) {
            return IndexActionOptionsReturnTypeEnum::XLSX;
        }

        return IndexActionOptionsReturnTypeEnum::Resource;
    }
}
