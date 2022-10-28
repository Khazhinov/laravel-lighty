<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Illuminate\Support\Facades\DB;
use JsonException;
use Khazhinov\LaravelLighty\Exceptions\Http\ActionResponseNotFoundException;
use Khazhinov\LaravelLighty\Http\Controllers\Api\ApiController;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\ActionClosureModeEnum;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\ActionOptionsDeleted;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\ActionOptionsDeletedModeEnum;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\ActionOptionsRelationships;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\ApiCRUDControllerActionInitDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\ApiCRUDControllerMetaDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\ApiCRUDControllerOptionDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\BulkDestroyAction\Option\BulkDestroyActionOptionsDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\BulkDestroyAction\Payload\BulkDestroyActionRequestPayloadDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\DestroyAction\Option\DestroyActionOptionsDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Option\IndexActionOptionsDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Option\IndexActionOptionsReturnTypeEnum;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Payload\IndexActionRequestPayloadDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Payload\IndexActionRequestPayloadFilterBooleanEnum;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Payload\IndexActionRequestPayloadFilterDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Payload\IndexActionRequestPayloadFilterTypeEnum;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\SetPositionAction\Option\SetPositionActionOptionsDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\SetPositionAction\Payload\SetPositionActionRequestPayloadDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\ShowAction\Option\ShowActionOptionsDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\StoreAction\Option\StoreActionOptionsDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\UpdateAction\Option\UpdateActionOptionsDTO;
use Khazhinov\LaravelLighty\Http\Requests\BaseRequest;
use Khazhinov\LaravelLighty\Http\Resources\CollectionResource;
use Khazhinov\LaravelLighty\Http\Resources\JsonResource;
use Khazhinov\LaravelLighty\Models\Attributes\Relationships\RelationshipTypeEnum;
use Khazhinov\LaravelLighty\Models\Model;
use Khazhinov\LaravelLighty\Transaction\WithDBTransactionInterface;
use Maatwebsite\Excel\Facades\Excel;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
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
    public function __construct(ApiCRUDControllerMetaDTO $controller_meta_dto)
    {
        parent::__construct();

        $this->controller_meta = $controller_meta_dto;
        $this->setCurrentModel($controller_meta_dto->model_class);
        $this->setSingleResource($controller_meta_dto->single_resource_class);
        $this->setCollectionResource($controller_meta_dto->collection_resource_class);

        if ($controller_meta_dto->hasAllowedRelationships()) {
            $this->setAllowedRelationships($controller_meta_dto->allowed_relationships);
        }
    }

    protected readonly ApiCRUDControllerMetaDTO $controller_meta;
    protected string $single_resource;
    protected string $collection_resource;

    /**
     * Controller base model.
     *
     * @var Model|\Khazhinov\LaravelLightyMongoDBBundle\Models\Model
     */
    protected $current_model;

    /**
     * Array of relationships.
     *
     * @var array<string>
     */
    protected array $allowed_relationships = [];

    /**
     * @return array<string>
     */
    abstract protected function getDefaultOrder(): array;

    /**
     * @return Builder|DatabaseBuilder
     */
    abstract protected function getQueryBuilder(): Builder|DatabaseBuilder;

    /**
     * Set controller base model.
     *
     * @param  Model|\Khazhinov\LaravelLightyMongoDBBundle\Models\Model|string  $current_model
     */
    protected function setCurrentModel(mixed $current_model): void
    {
        if (is_string($current_model)) {
            $current_model = new $current_model();
        }

        if (! is_a($current_model, Model::class, true)) {
            $tmp_class = $current_model::class;
            // MongoDB Bundle
            if (class_exists('\Khazhinov\LaravelLightyMongoDBBundle\Models\Model')) {
                if (! is_a($current_model, '\Khazhinov\LaravelLightyMongoDBBundle\Models\Model', true)) {
                    $base_class = '\Khazhinov\LaravelLightyMongoDBBundle\Models\Model';

                    throw new RuntimeException("Class $tmp_class must be inherited from class $base_class");
                }
            } else {
                $base_class = Model::class;

                throw new RuntimeException("Class $tmp_class must be inherited from class $base_class");
            }
        }

        $this->current_model = $current_model;
    }

    /**
     * @param  array<string>  $allowed_relationships
     * @return void
     */
    protected function setAllowedRelationships(array $allowed_relationships): void
    {
        /** @var array<string> $completed_allowed_relationships */
        $completed_allowed_relationships = [];
        foreach ($allowed_relationships as $relationship) {
            if ($relationship_completed = $this->current_model->completeRelation($relationship)) {
                /** @var string $relationship_completed */
                $completed_allowed_relationships[] = $relationship_completed;
            }
        }

        $this->allowed_relationships = $completed_allowed_relationships;
    }

    /**
     * @return array<string>
     */
    protected function getAllowedRelationships(): array
    {
        return $this->allowed_relationships;
    }

    /**
     * @param  string  $relationship
     * @return bool
     */
    protected function checkRelationship(string $relationship): bool
    {
        return in_array($relationship, $this->getAllowedRelationships(), true);
    }

    /**
     * @param  ApiCRUDControllerActionInitDTO  $action_init_dto
     * @return ApiCRUDControllerOptionDTO
     */
    protected function initFunction(ApiCRUDControllerActionInitDTO $action_init_dto): ApiCRUDControllerOptionDTO
    {
        $action_options_dto = $action_init_dto->getActionOptionDTO($this->controller_meta);
        $this->setOptions($action_options_dto->toArray());

        $this->setCurrentAction($action_init_dto->action_name);

        return $action_options_dto;
    }

    /**
     * @param  IndexActionOptionsDTO|array<string, mixed>  $options
     * @param  Closure|null  $closure
     * @return mixed
     * @throws UnknownProperties
     * @throws JsonException
     * @throws ReflectionException
     */
    protected function indexAction(BaseRequest $request, IndexActionOptionsDTO|array $options = [], Closure $closure = null): mixed
    {
        /** @var IndexActionOptionsDTO $current_options */
        $current_options = $this->initFunction(new ApiCRUDControllerActionInitDTO([
            'action_name' => 'index',
            'action_options' => $options,
            'action_option_class' => IndexActionOptionsDTO::class,
        ]));

        $builder = $this->getPreparedQueryBuilder($current_options);

        $current_request = new IndexActionRequestPayloadDTO($request->all());

        if ($current_options->filters->enable) {
            $builder = $this->addFilters($current_options, $current_request->filter, $builder);
        }

        if ($current_options->orders->enable) {
            $builder = $this->addOrders($current_request, $builder);
        }

        if ($current_options->relationships->enable) {
            $builder = $this->addRelationships($current_options->relationships, $current_request, $builder);
        }

        if ($closure) {
            $tmp_builder = $closure($builder, ActionClosureModeEnum::Builder);
            if ($tmp_builder) {
                $builder = $tmp_builder;
            }
        }

        if ($current_options->pagination->enable) {
            $limit = $current_request->limit;
            $page = $current_request->page;
            $items = $builder->paginate($limit, page: $page);
        } else {
            $items = $builder->get();
        }

        if ($closure && $filter_result = $closure($items, ActionClosureModeEnum::Filter)) {
            $items = $filter_result;
        }

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
            case IndexActionOptionsReturnTypeEnum::XLSX:
                $export_columns = $current_request->getExportColumns();

                if (! count($export_columns)) {
                    throw new RuntimeException('Requires specifying columns for export.');
                }

                $page_title = false;
                if (isset($limit, $page)) {
                    $page_title = helper_string_plural(
                        helper_string_title(
                            class_basename(
                                $this->current_model
                            )
                        )
                    );
                }

                return Excel::download(
                    new $current_options->export->exporter_class($items, $export_columns, $page_title),
                    $this->getExportFileName()
                );
            default:
                throw new RuntimeException('Undefined return type.');
        }
    }

    /**
     * Функция для получения подготовленного к работе QueryBuilder
     *
     * @param  ApiCRUDControllerOptionDTO  $options
     * @return Builder|DatabaseBuilder
     */
    protected function getPreparedQueryBuilder(ApiCRUDControllerOptionDTO $options): Builder|DatabaseBuilder
    {
        $builder = $this->getQueryBuilder();
        $builder = $this->sanitizeBuilder($builder);

        return $this->implementSoftDeleteIfNeed($builder, $options->deleted);
    }

    /**
     * Метод для очистки QueryBuilder от избыточных заготовленных условий
     *
     * @param  Builder|DatabaseBuilder  $builder
     * @return Builder|DatabaseBuilder
     */
    protected function sanitizeBuilder(Builder|DatabaseBuilder $builder): Builder|DatabaseBuilder
    {
        /** @var Builder $builder */
        if ($builder->hasMacro('withTrashed')) {
            $builder = $builder->withoutGlobalScope(SoftDeletingScope::class);
        }

        return $builder;
    }

    /**
     * Функция внедряет фильтрацию с учётом SoftDelete
     *
     * @param  Builder|DatabaseBuilder  $builder
     * @param  ActionOptionsDeleted  $options
     * @return Builder|DatabaseBuilder
     */
    protected function implementSoftDeleteIfNeed(Builder|DatabaseBuilder $builder, ActionOptionsDeleted $options): Builder|DatabaseBuilder
    {
        if ($options->enable) {
            $column = $this->current_model->getTable().'.'.$options->column;

            switch ($options->mode) {
                case ActionOptionsDeletedModeEnum::WithoutTrashed:
                    $builder = $builder->where(static function (Builder|DatabaseBuilder $builder) use ($column) {
                        $builder->whereNull($column);
                    });

                    break;
                case ActionOptionsDeletedModeEnum::WithTrashed:
                    break;
                case ActionOptionsDeletedModeEnum::OnlyTrashed:
                    $builder = $builder->where(static function (Builder|DatabaseBuilder $builder) use ($column) {
                        $builder->whereNotNull($column);
                    });

                    break;
            }
        }

        return $builder;
    }

    /**
     * @param  IndexActionOptionsDTO  $options
     * @param  IndexActionRequestPayloadFilterDTO[]  $filters
     * @param  Builder|DatabaseBuilder  $builder
     * @return Builder|DatabaseBuilder
     */
    protected function addFilters(IndexActionOptionsDTO $options, array $filters, Builder|DatabaseBuilder $builder): Builder|DatabaseBuilder
    {
        if (count($filters)) {
            $builder = $builder->where(function (Builder|DatabaseBuilder $builder) use ($options, $filters) {
                foreach ($filters as $filter) {
                    $builder = $this->addFilter($options, $builder, $filter);
                }
            });
        }

        return $builder;
    }

    /**
     * @param  IndexActionOptionsDTO  $options
     * @param  Builder|DatabaseBuilder  $builder
     * @param  IndexActionRequestPayloadFilterDTO  $filter
     * @return Builder|DatabaseBuilder
     */
    protected function addFilter(IndexActionOptionsDTO $options, Builder|DatabaseBuilder $builder, IndexActionRequestPayloadFilterDTO $filter): Builder|DatabaseBuilder
    {
        $ignore = $options->filters->ignore;
        if ($ignore && is_array($ignore) && in_array($filter->column, $ignore, true)) {
            return $builder;
        }

        if ($filter->type === IndexActionRequestPayloadFilterTypeEnum::Group) {
            $inside_function = function (Builder|DatabaseBuilder $builder) use ($options, $filter) {
                foreach ($filter->group as $inside_filter) {
                    $builder = $this->addFilter($options, $builder, $inside_filter);
                }
            };

            if ($filter->boolean === IndexActionRequestPayloadFilterBooleanEnum::And) {
                $builder = $builder->where($inside_function);
            } else {
                $builder = $builder->orWhere($inside_function);
            }

            return $builder;
        }

        $column = $filter->column;
        if (! $column) {
            throw new RuntimeException('Column field cannot be null.');
        }

        $operator = $filter->operator->value;
        $value = $filter->value;
        $boolean = $filter->boolean->value;

        if (! mb_stripos($column, '.')) {
            $column = $this->current_model->getTable().'.'.$column;
        }

        if (is_array($value)) {
            return $builder->whereIn($column, $value, $boolean, $operator !== '=');
        }

        return $builder->where($column, $operator, $value, $boolean);
    }

    /**
     * @param  IndexActionRequestPayloadDTO  $request
     * @param  Builder|DatabaseBuilder  $builder
     * @return Builder|DatabaseBuilder
     */
    protected function addOrders(IndexActionRequestPayloadDTO $request, Builder|DatabaseBuilder $builder): Builder|DatabaseBuilder
    {
        $orders = $request->order;

        if (! $orders) {
            $orders = $this->getDefaultOrder();
        }

        foreach ($orders as $order) {
            $builder = $this->addOrder($builder, htmlspecialchars($order, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        }

        return $builder;
    }

    /**
     * @param  Builder|DatabaseBuilder  $builder
     * @param  string  $order
     * @return Builder|DatabaseBuilder
     */
    protected function addOrder(Builder|DatabaseBuilder $builder, string $order): Builder|DatabaseBuilder
    {
        $direction = 'asc';

        if (str_starts_with($order, '-')) {
            $direction = 'desc';
            $order = substr($order, 1);
        }

        $builder->orderBy($order, $direction);

        return $builder;
    }

    /**
     * @param  ActionOptionsRelationships  $options
     * @param  IndexActionRequestPayloadDTO  $request
     * @param  Builder|DatabaseBuilder  $builder
     * @return Builder|DatabaseBuilder
     */
    protected function addRelationships(ActionOptionsRelationships $options, IndexActionRequestPayloadDTO $request, Builder|DatabaseBuilder $builder): Builder|DatabaseBuilder
    {
        if ($options->enable && $relationships = $request->with) {
            if (isset($relationships['relationships'])) {
                $relationships = $relationships['relationships'];
            } else {
                return $builder;
            }

            /** @var string $relationship */
            foreach ($relationships as $relationship) {
                if ($relationship_completed = $this->current_model->completeRelation($relationship)) {
                    /** @var string $relationship_completed */
                    $builder = $this->addRelationship($builder, $relationship_completed, $options->ignore_allowed);
                }
            }
        }

        return $builder;
    }

    /**
     * @param  Builder|DatabaseBuilder  $builder
     * @param  string  $relationship
     * @param  bool  $ignore_allowed
     * @return Builder|DatabaseBuilder
     */
    protected function addRelationship(Builder|DatabaseBuilder $builder, string $relationship, bool $ignore_allowed = false): Builder|DatabaseBuilder
    {
        /** @var Builder $builder */
        if ($ignore_allowed) {
            $builder = $builder->with($relationship);
        } else {
            if ($this->checkRelationship($relationship)) {
                $builder = $builder->with($relationship);
            }
        }

        return $builder;
    }

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
     * @param  SetPositionActionOptionsDTO|array<string, mixed>  $options
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws UnknownProperties
     * @throws Throwable
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

        $this->beginTransaction();

        try {
            $table = $this->current_model->getTable();
            $column = $current_options->position_column;
            $case = "CASE {$this->current_model->getKeyName()}";
            $sub_value = $current_request->ids;
            foreach ($sub_value as $i => $id) {
                $case .= " WHEN '$id' THEN $i";
                $sub_value[$i] = "'$id'";
            }
            $case .= ' END';
            $value = implode(',', $sub_value);
            $raw = strtolower("update $table set $column = $case where id in ($value)");

            DB::update($raw);
            $this->commit();

            return $this->respond(
                $this->buildActionResponseDTO(
                    data: [
                        'status' => 'ok',
                    ],
                )
            );
        } catch (Throwable $exception) {
            $this->rollback();

            throw $exception;
        }
    }

    /**
     * @param  mixed  $key
     * @param  ShowActionOptionsDTO|array<string, mixed>  $options
     * @return Response
     * @throws UnknownProperties
     * @throws JsonException
     * @throws ReflectionException
     */
    protected function showAction(mixed $key, ShowActionOptionsDTO|array $options = []): Response
    {
        /** @var ShowActionOptionsDTO $current_options */
        $current_options = $this->initFunction(new ApiCRUDControllerActionInitDTO([
            'action_name' => 'show',
            'action_options' => $options,
            'action_option_class' => ShowActionOptionsDTO::class,
        ]));

        $this->setCurrentModel(
            $this->getModelByKey(
                $current_options,
                $key
            )
        );

        $resource = $this->getSingleResource();

        /** @var JsonResource $result */
        $result = new $resource($this->current_model, true);

        return $this->respond(
            $this->buildActionResponseDTO(
                data: $result,
            )
        );
    }

    /**
     * Execute the query and get the first result or throw an exception.
     *
     * @param  ApiCRUDControllerOptionDTO  $options
     * @param  mixed  $key
     * @return Model
     * @throws ReflectionException
     * @throws UnknownProperties
     */
    protected function getModelByKey(ApiCRUDControllerOptionDTO $options, mixed $key): Model
    {
        $builder = $this->getPreparedQueryBuilder($options);

        $primary_key = $this->current_model->getKeyName();
        $column = $this->current_model->getTable().'.'.$primary_key;

        try {
            /** @var ?Model $model */
            $model = $builder->where($column, $key)->first();
        } catch (Throwable $exception) {
            // В случае, если поиск по идентификатору не дал должного результата, считаем, что модель Not Found
            throw new ActionResponseNotFoundException();
        }

        if (! $model) {
            throw new ActionResponseNotFoundException();
        }

        if ($options->relationships->enable) {
            $this->loadAllRelationshipsAfterGet();
        }

        return $model;
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws UnknownProperties
     */
    protected function loadAllRelationshipsAfterGet(): void
    {
        $this->current_model = $this->current_model->load(array_keys($this->current_model->getLocalRelations()));
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

        if ($closure) {
            $closure($this->current_model, ActionClosureModeEnum::BeforeFilling);
        }

        foreach ($this->current_model->getFillable() as $column) {
            if ($request->has($column)) {
                $this->current_model->setAttribute($column, $request->input($column));
            }
        }

        if ($closure) {
            $closure($this->current_model, ActionClosureModeEnum::AfterFilling);
        }

        $this->beginTransaction();

        try {
            $this->current_model->save();

            foreach ($this->current_model->getLocalRelations() as $relation_name => $relation_props) {
                if ($relation_props->type === RelationshipTypeEnum::BelongsToMany) {
                    $key = helper_string_snake($relation_name).'_ids';
                    if ($request->has($key)) {
                        $ids = $request->input($key);

                        $this->current_model->$relation_name()->sync(
                            ! is_null($ids) ? $ids : []
                        );
                    }
                }
            }

            if ($closure) {
                $closure($this->current_model, ActionClosureModeEnum::AfterSave);
            }

            $this->commit();
            $this->loadAllRelationshipsAfterGet();

            $resource = $this->getSingleResource();

            /** @var JsonResource $result */
            $result = new $resource($this->current_model, true);

            return $this->respond(
                $this->buildActionResponseDTO(
                    data: $result,
                )
            );
        } catch (Throwable $exception) {
            $this->rollback();

            throw $exception;
        }
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

        $this->setCurrentModel(
            $this->getModelByKey(
                options: $current_options,
                key: $key
            )
        );

        if ($closure) {
            $closure($this->current_model, ActionClosureModeEnum::BeforeFilling);
        }

        foreach ($this->current_model->getFillable() as $column) {
            if ($request->has($column)) {
                $this->current_model->setAttribute($column, $request->input($column));
            }
        }

        if ($closure) {
            $closure($this->current_model, ActionClosureModeEnum::AfterFilling);
        }

        $this->beginTransaction();

        try {
            $this->current_model->save();

            if ($closure) {
                $closure($this->current_model, ActionClosureModeEnum::AfterSave);
            }

            foreach ($this->current_model->getLocalRelations() as $relation_name => $relation_props) {
                if ($relation_props->type === RelationshipTypeEnum::BelongsToMany) {
                    $key = helper_string_snake($relation_name).'_ids';
                    if ($this->request->has($key)) {
                        $ids = $this->request->input($key);

                        $this->current_model->$relation_name()->sync(
                            ! is_null($ids) ? $ids : []
                        );
                    }
                }
            }

            $this->commit();
            $this->loadAllRelationshipsAfterGet();

            $resource = $this->getSingleResource();

            /** @var JsonResource $result */
            $result = new $resource($this->current_model, true);

            return $this->respond(
                $this->buildActionResponseDTO(
                    data: $result,
                )
            );
        } catch (Throwable $exception) {
            $this->rollback();

            throw $exception;
        }
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

        $this->setCurrentModel(
            $this->getModelByKey(
                options: $current_options,
                key: $key
            )
        );

        if ($closure) {
            $closure($this->current_model, ActionClosureModeEnum::AfterFilling);
        }

        $this->beginTransaction();

        try {
            if ($closure) {
                $closure($this->current_model, ActionClosureModeEnum::BeforeDeleting);
            }

            if ($current_options->deleted->enable && $current_options->force) {
                foreach ($this->current_model->getLocalRelations() as $relation_name => $relation_props) {
                    if ($relation_props->type === RelationshipTypeEnum::BelongsToMany) {
                        /** @var BelongsToMany $relation */
                        $relation = $this->current_model->$relation_name();
                        DB::table($relation->getTable())
                            ->whereIn($relation->getForeignPivotKeyName(), [$key])
                            ->delete();
                    }
                    if ($relation_props->type === RelationshipTypeEnum::HasMany) {
                        /** @var HasMany $relation */
                        $relation = $this->current_model->$relation_name();
                        $tmp_key = $relation->getExistenceCompareKey();
                        /** @var int $dot_position */
                        $dot_position = mb_stripos($tmp_key, '.');
                        $table = mb_substr($tmp_key, 0, $dot_position);
                        $foreign_key = mb_substr($tmp_key, $dot_position + 1, mb_strlen($tmp_key));
                        DB::table($table)
                            ->whereIn($foreign_key, [$key])
                            ->update([
                                $foreign_key => null,
                            ]);
                    }
                }

                $this->current_model->forceDelete();
            } else {
                $this->current_model->delete();
            }

            if ($closure) {
                $closure($this->current_model, ActionClosureModeEnum::AfterDeleting);
            }

            $this->commit();

            return $this->respond(
                $this->buildActionResponseDTO(
                    data: [
                        'status' => 'ok',
                    ],
                )
            );
        } catch (Throwable $exception) {
            $this->rollback();

            throw $exception;
        }
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

        $this->beginTransaction();

        try {
            if ($closure) {
                $closure($this->current_model, ActionClosureModeEnum::BeforeDeleting);
            }

            if ($current_options->deleted->enable && $current_options->force) {
                foreach ($this->current_model->getLocalRelations() as $relation_name => $relation_props) {
                    if ($relation_props->type === RelationshipTypeEnum::BelongsToMany) {
                        /** @var BelongsToMany $relation */
                        $relation = $this->current_model->$relation_name();
                        DB::table($relation->getTable())
                            ->whereIn(
                                $relation->getForeignPivotKeyName(),
                                $current_request->ids
                            )
                            ->delete();
                    }
                    if ($relation_props->type === RelationshipTypeEnum::HasMany) {
                        /** @var HasMany $relation */
                        $relation = $this->current_model->$relation_name();
                        $tmp_key = $relation->getExistenceCompareKey();
                        /** @var int $dot_position */
                        $dot_position = mb_stripos($tmp_key, '.');
                        $table = mb_substr($tmp_key, 0, $dot_position);
                        $foreign_key = mb_substr($tmp_key, $dot_position + 1, mb_strlen($tmp_key));
                        DB::table($table)
                            ->whereIn($foreign_key, $current_request->ids)
                            ->update([
                                $foreign_key => null,
                            ]);
                    }
                }


                $builder = $this->getQueryBuilder();
                /** @var Builder $builder */
                $builder = $builder->whereIn($this->current_model->getKey(), $current_request->ids);

                $builder->forceDelete();
            } else {
                $this->current_model::destroy($current_request->ids);
            }

            if ($closure) {
                $closure($this->current_model, ActionClosureModeEnum::AfterDeleting);
            }

            $this->commit();

            return $this->respond(
                $this->buildActionResponseDTO(
                    data: [
                        'status' => 'ok',
                    ],
                )
            );
        } catch (Throwable $exception) {
            $this->rollback();

            throw $exception;
        }
    }
}
