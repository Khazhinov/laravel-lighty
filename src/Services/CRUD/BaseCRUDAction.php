<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Services\CRUD;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Khazhinov\LaravelLighty\Exceptions\Http\ActionResponseNotFoundException;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\ActionOptionsDeleted;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\ActionOptionsDeletedModeEnum;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\BaseCRUDOptionDTO;
use Khazhinov\LaravelLighty\Models\Model;
use Khazhinov\LaravelLighty\Services\CRUD\Events\BaseCRUDEvent;
use Khazhinov\LaravelLighty\Services\CRUD\Events\CRUDEvent;
use Khazhinov\LaravelLighty\Services\CRUD\Exceptions\UndefinedCRUDEventException;
use Khazhinov\LaravelLighty\Services\CRUD\Exceptions\UnsupportedModelException;
use Khazhinov\LaravelLighty\Transaction\WithDBTransaction;
use Khazhinov\LaravelLighty\Transaction\WithDBTransactionInterface;
use ReflectionException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Throwable;

abstract class BaseCRUDAction implements WithDBTransactionInterface
{
    use WithDBTransaction;

    /**
     * Модель, для которой выполняется действие.
     *
     * @var Model|\Khazhinov\LaravelLightyMongoDBBundle\Models\Model
     */
    protected Model|\Khazhinov\LaravelLightyMongoDBBundle\Models\Model $currentModel;

    public function __construct(Model|\Khazhinov\LaravelLightyMongoDBBundle\Models\Model|string $model)
    {
        $this->setCurrentModel($model);
    }

    /**
     * Set controller base model.
     *
     * @param  Model|\Khazhinov\LaravelLightyMongoDBBundle\Models\Model|string  $currentModel
     */
    public function setCurrentModel(mixed $currentModel): void
    {
        if (is_string($currentModel)) {
            $currentModel = new $currentModel();
        }

        if (! is_a($currentModel, Model::class, true)) {
            $tmp_class = $currentModel::class;
            $mongodb_bundle_base_model_class = '\Khazhinov\LaravelLightyMongoDBBundle\Models\Model';
            // MongoDB Bundle
            if (class_exists($mongodb_bundle_base_model_class)) {
                if (! is_a($currentModel, $mongodb_bundle_base_model_class, true)) {
                    throw new UnsupportedModelException($tmp_class, $mongodb_bundle_base_model_class);
                }
            } else {
                $base_class = Model::class;

                throw new UnsupportedModelException($tmp_class, $base_class);
            }
        }

        $this->currentModel = $currentModel;
    }

    /**
     * @param  class-string  $needle_event_class
     * @param  mixed  ...$args
     * @return BaseCRUDEvent[]
     */
    public function getEvents(string $needle_event_class, ...$args): array
    {
        if (! is_a($needle_event_class, BaseCRUDEvent::class, true)) {
            throw new UndefinedCRUDEventException();
        }

        return [
            new CRUDEvent($needle_event_class, ...$args),
            new $needle_event_class(...$args),
        ];
    }

    /**
     * Функция для получения подготовленного к работе QueryBuilder
     *
     * @param  Builder|DatabaseBuilder  $base_builder
     * @param  BaseCRUDOptionDTO  $options
     * @return Builder|DatabaseBuilder
     */
    protected function getPreparedQueryBuilder(Builder|DatabaseBuilder $base_builder, BaseCRUDOptionDTO $options): Builder|DatabaseBuilder
    {
        $builder = $this->sanitizeBuilder($base_builder);

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
            $column = $this->currentModel->getTable().'.'.$options->column;

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
     * Поиск сущности по заданному значению ключа.
     *
     * @param  BaseCRUDOptionDTO  $options
     * @param  mixed  $key
     * @return Model
     * @throws ReflectionException
     * @throws UnknownProperties
     */
    protected function getModelByKey(BaseCRUDOptionDTO $options, mixed $key): Model
    {
        $builder = $this->getPreparedQueryBuilder($this->currentModel::query(), $options);

        $primary_key = $this->currentModel->getKeyName();
        $column = $this->currentModel->getTable().'.'.$primary_key;

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
     * Загрузка всех отношений в модели
     *
     * @return void
     * @throws ReflectionException
     * @throws UnknownProperties
     */
    protected function loadAllRelationshipsAfterGet(): void
    {
        $this->currentModel = $this->currentModel->load(array_keys($this->currentModel->getLocalRelations()));
    }
}
