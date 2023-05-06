<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Services\CRUD;

use Closure;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\ActionClosureModeEnum;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\StoreAction\Option\StoreActionOptionsDTO;
use Khazhinov\LaravelLighty\Models\Attributes\Relationships\RelationshipTypeEnum;
use Khazhinov\LaravelLighty\Models\Model;
use Khazhinov\LaravelLighty\Services\CRUD\DTO\ActionClosureDataDTO;
use Khazhinov\LaravelLighty\Services\CRUD\Events\Store\StoreCalled;
use Khazhinov\LaravelLighty\Services\CRUD\Events\Store\StoreEnded;
use Khazhinov\LaravelLighty\Services\CRUD\Events\Store\StoreError;
use ReflectionException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Throwable;

class StoreAction extends BaseCRUDAction
{
    /**
     * Функция создания сущности
     *
     * @param  StoreActionOptionsDTO  $options
     * @param  array<string, mixed>  $data
     * @param  Closure|null  $closure
     * @return Model
     * @throws ReflectionException
     * @throws Throwable
     * @throws UnknownProperties
     */
    public function handle(StoreActionOptionsDTO $options, array $data, Closure $closure = null): Model
    {
        event(new StoreCalled(
            modelClass: $this->currentModel::class,
            data: $data,
        ));

        $current_model_class = $this->currentModel::class;
        /** @var Model $new_model */
        $new_model = new $current_model_class();

        if ($closure) {
            $closure(new ActionClosureDataDTO([
                'mode' => ActionClosureModeEnum::BeforeFilling,
                'data' => $new_model,
            ]));
        }

        foreach ($new_model->getFillable() as $column) {
            if (array_key_exists($column, $data)) {
                $new_model->setAttribute($column, $data[$column]);
            }
        }

        if ($closure) {
            $closure(new ActionClosureDataDTO([
                'mode' => ActionClosureModeEnum::AfterFilling,
                'data' => $new_model,
            ]));
        }

        $this->beginTransaction();

        try {
            $new_model->save();

            foreach ($new_model->getLocalRelations() as $relation_name => $relation_props) {
                if ($relation_props->type === RelationshipTypeEnum::BelongsToMany) {
                    $key = helper_string_snake($relation_name).'_ids';
                    if (array_key_exists($key, $data)) {
                        $ids = $data[$key];

                        $new_model->$relation_name()->sync(
                            ! is_null($ids) ? $ids : []
                        );
                    }
                }
            }

            if ($closure) {
                $closure(new ActionClosureDataDTO([
                    'mode' => ActionClosureModeEnum::AfterSave,
                    'data' => $new_model,
                ]));
            }

            $this->commit();

            if ($closure) {
                $closure(new ActionClosureDataDTO([
                    'mode' => ActionClosureModeEnum::AfterCommit,
                    'data' => $new_model,
                ]));
            }

            $this->loadAllRelationshipsAfterGet();

            event(new StoreEnded(
                modelClass: $this->currentModel::class,
                data: $new_model,
            ));

            return $new_model;
        } catch (Throwable $exception) {
            event(new StoreError(
                modelClass: $this->currentModel::class,
                data: $new_model,
                exception: $exception,
            ));

            if ($closure) {
                $closure(new ActionClosureDataDTO([
                    'mode' => ActionClosureModeEnum::BeforeRollback,
                    'data' => $new_model,
                    'exception' => $exception,
                ]));
            }

            $this->rollback();

            if ($closure) {
                $closure(new ActionClosureDataDTO([
                    'mode' => ActionClosureModeEnum::AfterRollback,
                    'data' => $new_model,
                    'exception' => $exception,
                ]));
            }

            throw $exception;
        }
    }
}
