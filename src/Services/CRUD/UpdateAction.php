<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Services\CRUD;

use Closure;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\ActionClosureModeEnum;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\UpdateAction\Option\UpdateActionOptionsDTO;
use Khazhinov\LaravelLighty\Models\Attributes\Relationships\RelationshipTypeEnum;
use Khazhinov\LaravelLighty\Models\Model;
use Khazhinov\LaravelLighty\Services\CRUD\DTO\ActionClosureDataDTO;
use Khazhinov\LaravelLighty\Services\CRUD\Events\Update\UpdateCalled;
use Khazhinov\LaravelLighty\Services\CRUD\Events\Update\UpdateEnded;
use Khazhinov\LaravelLighty\Services\CRUD\Events\Update\UpdateError;
use ReflectionException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Throwable;

class UpdateAction extends BaseCRUDAction
{
    /**
     * Функция изменения сущности
     *
     * @param  UpdateActionOptionsDTO  $options
     * @param  string  $key
     * @param  array<string, mixed>  $data
     * @param  Closure|null  $closure
     * @return Model
     * @throws ReflectionException
     * @throws Throwable
     * @throws UnknownProperties
     */
    public function handle(UpdateActionOptionsDTO $options, string $key, array $data, Closure $closure = null): Model
    {
        $current_model = $this->getModelByKey($options, $key);

        event(...$this->getEvents(
            needle_event_class: UpdateCalled::class,
            modelClass: $this->currentModel::class,
            data: $current_model,
        ));

        if ($closure) {
            $closure(new ActionClosureDataDTO([
                'mode' => ActionClosureModeEnum::BeforeFilling,
                'data' => $current_model,
            ]));
        }

        foreach ($current_model->getFillable() as $column) {
            if (array_key_exists($column, $data)) {
                $current_model->setAttribute($column, $data[$column]);
            }
        }

        if ($closure) {
            $closure(new ActionClosureDataDTO([
                'mode' => ActionClosureModeEnum::AfterFilling,
                'data' => $current_model,
            ]));
        }

        $this->beginTransaction();

        try {
            $current_model->save();

            if ($closure) {
                $closure(new ActionClosureDataDTO([
                    'mode' => ActionClosureModeEnum::AfterSave,
                    'data' => $current_model,
                ]));
            }

            foreach ($current_model->getLocalRelations() as $relation_name => $relation_props) {
                if ($relation_props->type === RelationshipTypeEnum::BelongsToMany) {
                    $key = helper_string_snake($relation_name).'_ids';
                    if (array_key_exists($key, $data)) {
                        $ids = $data[$key];

                        $current_model->$relation_name()->sync(
                            ! is_null($ids) ? $ids : []
                        );
                    }
                }
            }

            $this->commit();

            if ($closure) {
                $closure(new ActionClosureDataDTO([
                    'mode' => ActionClosureModeEnum::AfterCommit,
                    'data' => $current_model,
                ]));
            }

            $this->loadAllRelationshipsAfterGet();

            event(...$this->getEvents(
                needle_event_class: UpdateEnded::class,
                modelClass: $this->currentModel::class,
                data: $current_model,
            ));

            return $current_model;
        } catch (Throwable $exception) {
            event(...$this->getEvents(
                needle_event_class: UpdateError::class,
                modelClass: $this->currentModel::class,
                data: $current_model,
                exception: $exception,
            ));

            if ($closure) {
                $closure(new ActionClosureDataDTO([
                    'mode' => ActionClosureModeEnum::BeforeRollback,
                    'data' => $current_model,
                    'exception' => $exception,
                ]));
            }

            $this->rollback();

            if ($closure) {
                $closure(new ActionClosureDataDTO([
                    'mode' => ActionClosureModeEnum::AfterRollback,
                    'data' => $current_model,
                    'exception' => $exception,
                ]));
            }

            throw $exception;
        }
    }
}
