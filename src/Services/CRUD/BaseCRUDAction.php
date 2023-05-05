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
    protected Model|\Khazhinov\LaravelLightyMongoDBBundle\Models\Model $current_model;

    /**
     * Список разрешенных отношений для загрузки.
     *
     * @var array<string>
     */
    protected array $allowed_relationships = [];

    /**
     * Set controller base model.
     *
     * @param  Model|\Khazhinov\LaravelLightyMongoDBBundle\Models\Model|string  $current_model
     */
    public function setCurrentModel(mixed $current_model): void
    {
        if (is_string($current_model)) {
            $current_model = new $current_model();
        }

        if (! is_a($current_model, Model::class, true)) {
            $tmp_class = $current_model::class;
            $mongodb_bundle_base_model_class = '\Khazhinov\LaravelLightyMongoDBBundle\Models\Model';
            // MongoDB Bundle
            if (class_exists($mongodb_bundle_base_model_class)) {
                if (! is_a($current_model, $mongodb_bundle_base_model_class, true)) {
                    throw new UnsupportedModelException($tmp_class, $mongodb_bundle_base_model_class);
                }
            } else {
                $base_class = Model::class;

                throw new UnsupportedModelException($tmp_class, $base_class);
            }
        }

        $this->current_model = $current_model;
    }

    /**
     * @param  array<string>  $allowed_relationships
     * @return void
     */
    public function setAllowedRelationships(array $allowed_relationships): void
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
        $builder = $this->getPreparedQueryBuilder($this->current_model::query(), $options);

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
     * Загрузка всех отношений в модели
     *
     * @return void
     * @throws ReflectionException
     * @throws UnknownProperties
     */
    protected function loadAllRelationshipsAfterGet(): void
    {
        $this->current_model = $this->current_model->load(array_keys($this->current_model->getLocalRelations()));
    }
}
