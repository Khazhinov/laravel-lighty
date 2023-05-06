<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use JsonException;
use Khazhinov\LaravelLighty\Http\Controllers\Api\ApiController;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\ApiCRUDControllerActionInitDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\ApiCRUDControllerMetaDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\BaseCRUDOptionDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\BulkDestroyAction\Option\BulkDestroyActionOptionsDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\BulkDestroyAction\Payload\BulkDestroyActionRequestPayloadDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\DestroyAction\Option\DestroyActionOptionsDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Option\IndexActionOptionsDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Option\IndexActionOptionsExportExportTypeEnum;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Option\IndexActionOptionsReturnTypeEnum;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Payload\IndexActionRequestPayloadDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\SetPositionAction\Option\SetPositionActionOptionsDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\SetPositionAction\Payload\SetPositionActionRequestPayloadDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\ShowAction\Option\ShowActionOptionsDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\StoreAction\Option\StoreActionOptionsDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\UpdateAction\Option\UpdateActionOptionsDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\Exceptions\UndefinedActionClassException;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\Exceptions\UndefinedExportTypeException;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\Exceptions\UndefinedReturnTypeException;
use Khazhinov\LaravelLighty\Http\Requests\BaseRequest;
use Khazhinov\LaravelLighty\Http\Resources\CollectionResource;
use Khazhinov\LaravelLighty\Http\Resources\JsonResource;
use Khazhinov\LaravelLighty\Models\Model;
use Khazhinov\LaravelLighty\Services\CRUD\BaseCRUDAction;
use Khazhinov\LaravelLighty\Services\CRUD\BulkDestroyAction;
use Khazhinov\LaravelLighty\Services\CRUD\DestroyAction;
use Khazhinov\LaravelLighty\Services\CRUD\IndexAction;
use Khazhinov\LaravelLighty\Services\CRUD\SetPositionAction;
use Khazhinov\LaravelLighty\Services\CRUD\ShowAction;
use Khazhinov\LaravelLighty\Services\CRUD\StoreAction;
use Khazhinov\LaravelLighty\Services\CRUD\UpdateAction;
use Khazhinov\LaravelLighty\Transaction\WithDBTransactionInterface;
use Maatwebsite\Excel\Facades\Excel;
use ReflectionException;
use RuntimeException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @method static Builder|DatabaseBuilder withTrashed(bool $withTrashed = true)
 * @method static Builder|DatabaseBuilder onlyTrashed()
 * @method static Builder|DatabaseBuilder withoutTrashed()
 * @method static Builder|DatabaseBuilder forceDelete()
 */
abstract class ApiCRUDController extends ApiController implements WithDBTransactionInterface
{
    /**
     * Модель, для которой выполняется действие.
     *
     * @var class-string|Model
     */
    protected mixed $current_model;

    /**
     * Список разрешенных отношений для загрузки.
     *
     * @var array<string>
     */
    protected array $allowed_relationships = [];

    public function __construct(ApiCRUDControllerMetaDTO $controller_meta_dto)
    {
        parent::__construct();

        $this->controller_meta = $controller_meta_dto;
        $this->current_model = $controller_meta_dto->model_class;
        $this->setSingleResource($controller_meta_dto->single_resource_class);
        $this->setCollectionResource($controller_meta_dto->collection_resource_class);

        if ($controller_meta_dto->hasAllowedRelationships()) {
            $this->allowed_relationships = $controller_meta_dto->allowed_relationships;
        }
    }

    protected readonly ApiCRUDControllerMetaDTO $controller_meta;
    protected string $single_resource;
    protected string $collection_resource;

    /**
     * Get collection of models resource
     *
     * @return string
     */
    protected function getCollectionResource(): string
    {
        return $this->collection_resource;
    }

    /**
     * Set collection of models resource.
     *
     * @param  string  $collection_resource
     * @return void
     */
    protected function setCollectionResource(string $collection_resource): void
    {
        $this->collection_resource = $collection_resource;
    }

    /**
     * Get single model resource.
     *
     * @return string
     */
    protected function getSingleResource(): string
    {
        return $this->single_resource;
    }

    /**
     * Set single model resource.
     *
     * @param  string  $resource
     * @return void
     */
    protected function setSingleResource(string $resource): void
    {
        $this->single_resource = $resource;
    }

    /**
     * @param  ApiCRUDControllerActionInitDTO  $action_init_dto
     * @return BaseCRUDOptionDTO
     */
    protected function initFunction(ApiCRUDControllerActionInitDTO $action_init_dto): BaseCRUDOptionDTO
    {
        $action_options_dto = $action_init_dto->getActionOptionDTO($this->controller_meta);
        $this->setOptions($action_options_dto->toArray());

        $this->setCurrentAction($action_init_dto->action_name);

        return $action_options_dto;
    }

    /**
     * @param  class-string  $action_class
     * @return BaseCRUDAction
     */
    protected function getAction(string $action_class): BaseCRUDAction
    {
        if (! is_a($action_class, BaseCRUDAction::class, true)) {
            throw new UndefinedActionClassException();
        }

        return new $action_class($this->current_model);
    }

    /**
     * @param  BaseRequest  $request
     * @param  Builder|DatabaseBuilder|null  $builder
     * @param  IndexActionOptionsDTO|array<string, mixed>  $options
     * @param  Closure|null  $closure
     * @return mixed
     * @throws JsonException
     * @throws ReflectionException
     * @throws UnknownProperties
     */
    protected function indexAction(BaseRequest $request, Builder|DatabaseBuilder $builder = null, IndexActionOptionsDTO|array $options = [], Closure $closure = null): mixed
    {
        /** @var IndexActionOptionsDTO $current_options */
        $current_options = $this->initFunction(new ApiCRUDControllerActionInitDTO([
            'action_name' => 'index',
            'action_options' => $options,
            'action_option_class' => IndexActionOptionsDTO::class,
        ]));

        $current_request = new IndexActionRequestPayloadDTO($request->all());

        /** @var IndexAction $index_action */
        $index_action = $this->getAction(IndexAction::class);
        $index_action->setAllowedRelationships($this->allowed_relationships);
        $items = $index_action->handle(
            builder: $builder,
            options: $current_options,
            data: $current_request,
            closure: $closure
        );

        switch ($current_options->getReturnTypeByRequestPayload($current_request)) {
            case IndexActionOptionsReturnTypeEnum::Resource:
                $resource = $this->getCollectionResource();

                if (! $single_resource_class = $current_options->single_resource_class) {
                    /** @var CollectionResource $result */
                    $result = new $resource($items);
                } else {
                    /** @var CollectionResource $result */
                    $result = new $resource($items, $single_resource_class);
                }

                return $this->respond(
                    $this->buildActionResponseDTO(
                        data: $result,
                    )
                );
            case IndexActionOptionsReturnTypeEnum::Export:
                if ($current_request->export->export_type) {
                    $export_type = IndexActionOptionsExportExportTypeEnum::from($current_request->export->export_type);
                } else {
                    $export_type = $current_options->export->default_export_type;
                }

                $export_columns = $current_request->getExportColumns();

                if (! count($export_columns)) {
                    throw new RuntimeException('Requires specifying columns for export.');
                }

                switch ($export_type) {
                    case IndexActionOptionsExportExportTypeEnum::XLSX:
                        if (isset($request->export['file_name']) && ! empty($request->export['file_name'])) {
                            $file_name = sprintf('%s.xlsx', $request->export['file_name']);
                        } else {
                            $file_name = $this->getExportFileName();
                        }

                        return Excel::download(
                            new $current_options->export->exporter_class($items, $export_columns, false),
                            $file_name
                        );
                    case IndexActionOptionsExportExportTypeEnum::CSV:
                        if (isset($request->export['file_name']) && ! empty($request->export['file_name'])) {
                            $file_name = sprintf('%s.csv', $request->export['file_name']);
                        } else {
                            $file_name = $this->getExportFileName();
                        }

                        return Excel::download(
                            new $current_options->export->exporter_class($items, $export_columns, false),
                            $file_name,
                            \Maatwebsite\Excel\Excel::CSV,
                        );
                    default:
                        throw new UndefinedExportTypeException();
                }
                // no break
            default:
                throw new UndefinedReturnTypeException();
        }
    }

    /**
     * @param  string  $return_type
     * @return string
     */
    protected function getExportFileName(string $return_type = 'xlsx'): string
    {
        $model_name = class_basename($this->current_model);
        $date = now()->format('_Y-M-d');

        return helper_string_ucfirst((string) helper_string_plural((string) helper_string_snake($model_name))).$date.'.'.$return_type;
    }

    /**
     * @param  BaseRequest  $request
     * @param  SetPositionActionOptionsDTO|array<string, mixed>  $options
     * @return Response
     * @throws JsonException
     * @throws ReflectionException
     * @throws Throwable
     * @throws UnknownProperties
     */
    protected function setPositionAction(BaseRequest $request, SetPositionActionOptionsDTO|array $options = []): Response
    {
        /** @var SetPositionActionOptionsDTO $current_options */
        $current_options = $this->initFunction(new ApiCRUDControllerActionInitDTO([
            'action_name' => 'set_positions',
            'action_options' => $options,
            'action_option_class' => SetPositionActionOptionsDTO::class,
        ]));

        $current_request = new SetPositionActionRequestPayloadDTO($request->all());

        /** @var SetPositionAction $set_position_action */
        $set_position_action = $this->getAction(SetPositionAction::class);
        $set_position_action->handle($current_options, $current_request);

        return $this->respond(
            $this->buildActionResponseDTO(
                data: [
                    'status' => 'ok',
                ],
            )
        );
    }

    /**
     * @param  mixed  $key
     * @param  ShowActionOptionsDTO|array<string, mixed>  $options
     * @return Response
     * @throws UnknownProperties
     * @throws JsonException
     * @throws ReflectionException
     * @throws Throwable
     */
    protected function showAction(mixed $key, ShowActionOptionsDTO|array $options = []): Response
    {
        /** @var ShowActionOptionsDTO $current_options */
        $current_options = $this->initFunction(new ApiCRUDControllerActionInitDTO([
            'action_name' => 'show',
            'action_options' => $options,
            'action_option_class' => ShowActionOptionsDTO::class,
        ]));

        /** @var ShowAction $show_action */
        $show_action = $this->getAction(ShowAction::class);
        $model = $show_action->handle($current_options, $key);
        $resource = $this->getSingleResource();

        /** @var JsonResource $result */
        $result = new $resource($model, true);

        return $this->respond(
            $this->buildActionResponseDTO(
                data: $result,
            )
        );
    }

    /**
     * @param  BaseRequest  $request
     * @param  StoreActionOptionsDTO|array<string, mixed>  $options
     * @param  Closure|null  $closure
     * @return Response
     * @throws JsonException
     * @throws ReflectionException
     * @throws Throwable
     * @throws UnknownProperties
     */
    protected function storeAction(BaseRequest $request, StoreActionOptionsDTO|array $options = [], Closure $closure = null): Response
    {
        /** @var StoreActionOptionsDTO $current_options */
        $current_options = $this->initFunction(new ApiCRUDControllerActionInitDTO([
            'action_name' => 'store',
            'action_options' => $options,
            'action_option_class' => StoreActionOptionsDTO::class,
        ]));

        /** @var StoreAction $store_action */
        $store_action = $this->getAction(StoreAction::class);
        $model = $store_action->handle(
            options: $current_options,
            data: $request->validated(),
            closure: $closure,
        );

        $resource = $this->getSingleResource();

        /** @var JsonResource $result */
        $result = new $resource($model, true);

        return $this->respond(
            $this->buildActionResponseDTO(
                data: $result,
            )
        );
    }

    /**
     * @param  BaseRequest  $request
     * @param  mixed  $key
     * @param  UpdateActionOptionsDTO|array<string, mixed>  $options
     * @param  Closure|null  $closure
     * @return Response
     * @throws JsonException
     * @throws ReflectionException
     * @throws Throwable
     * @throws UnknownProperties
     */
    protected function updateAction(BaseRequest $request, mixed $key, UpdateActionOptionsDTO|array $options = [], Closure $closure = null): Response
    {
        /** @var UpdateActionOptionsDTO $current_options */
        $current_options = $this->initFunction(new ApiCRUDControllerActionInitDTO([
            'action_name' => 'update',
            'action_options' => $options,
            'action_option_class' => UpdateActionOptionsDTO::class,
        ]));

        /** @var UpdateAction $update_action */
        $update_action = $this->getAction(UpdateAction::class);
        $updated_model = $update_action->handle(
            options: $current_options,
            key: $key,
            data: $request->validated(),
            closure: $closure
        );

        $resource = $this->getSingleResource();

        /** @var JsonResource $result */
        $result = new $resource($updated_model, true);

        return $this->respond(
            $this->buildActionResponseDTO(
                data: $result,
            )
        );
    }

    /**
     * @param  mixed  $key
     * @param  DestroyActionOptionsDTO|array<string, mixed>  $options
     * @param  Closure|null  $closure
     * @return Response
     * @throws JsonException
     * @throws ReflectionException
     * @throws Throwable
     * @throws UnknownProperties
     */
    protected function destroyAction(mixed $key, DestroyActionOptionsDTO|array $options = [], Closure $closure = null): Response
    {
        /** @var DestroyActionOptionsDTO $current_options */
        $current_options = $this->initFunction(new ApiCRUDControllerActionInitDTO([
            'action_name' => 'destroy',
            'action_options' => $options,
            'action_option_class' => DestroyActionOptionsDTO::class,
        ]));

        /** @var DestroyAction $destroy_action */
        $destroy_action = $this->getAction(DestroyAction::class);
        $destroy_action->handle(
            options: $current_options,
            key: $key,
            closure: $closure,
        );

        return $this->respond(
            $this->buildActionResponseDTO(
                data: [
                    'status' => 'ok',
                ],
            )
        );
    }

    /**
     * @param  BaseRequest  $request
     * @param  BulkDestroyActionOptionsDTO|array<string, mixed>  $options
     * @param  Closure|null  $closure
     * @return Response
     * @throws JsonException
     * @throws ReflectionException
     * @throws Throwable
     * @throws UnknownProperties
     */
    protected function bulkDestroyAction(BaseRequest $request, BulkDestroyActionOptionsDTO|array $options = [], Closure $closure = null): Response
    {
        /** @var BulkDestroyActionOptionsDTO $current_options */
        $current_options = $this->initFunction(new ApiCRUDControllerActionInitDTO([
            'action_name' => 'bulk_destroy',
            'action_options' => $options,
            'action_option_class' => BulkDestroyActionOptionsDTO::class,
        ]));

        $current_request = new BulkDestroyActionRequestPayloadDTO($request->all());

        /** @var BulkDestroyAction $bulk_destroy_action */
        $bulk_destroy_action = $this->getAction(BulkDestroyAction::class);
        $bulk_destroy_action->handle(
            options: $current_options,
            data: $current_request,
            closure: $closure
        );

        return $this->respond(
            $this->buildActionResponseDTO(
                data: [
                    'status' => 'ok',
                ],
            )
        );
    }
}
