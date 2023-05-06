<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Services\CRUD;

use Closure;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\ActionClosureModeEnum;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\DestroyAction\Option\DestroyActionOptionsDTO;
use Khazhinov\LaravelLighty\Models\Attributes\Relationships\RelationshipTypeEnum;
use Khazhinov\LaravelLighty\Services\CRUD\DTO\ActionClosureDataDTO;
use Khazhinov\LaravelLighty\Services\CRUD\Events\Destroy\DestroyCalled;
use Khazhinov\LaravelLighty\Services\CRUD\Events\Destroy\DestroyEnded;
use Khazhinov\LaravelLighty\Services\CRUD\Events\Destroy\DestroyError;
use ReflectionException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Throwable;

class DestroyAction extends BaseCRUDAction
{
    /**
     * Функция удаления сущности
     *
     * @param  DestroyActionOptionsDTO  $options
     * @param  mixed  $key
     * @param  Closure|null  $closure
     * @return bool
     * @throws ReflectionException
     * @throws Throwable
     * @throws UnknownProperties
     */
    public function handle(DestroyActionOptionsDTO $options, mixed $key, Closure $closure = null): bool
    {
        $current_model = $this->getModelByKey(
            $options,
            $key
        );

        event(new DestroyCalled(
            modelClass: $this->currentModel::class,
            data: $current_model,
        ));

        if ($closure) {
            $closure(new ActionClosureDataDTO([
                'mode' => ActionClosureModeEnum::AfterFilling,
                'data' => $current_model,
            ]));
        }

        $this->beginTransaction();

        try {
            if ($closure) {
                $closure(new ActionClosureDataDTO([
                    'mode' => ActionClosureModeEnum::BeforeDeleting,
                    'data' => $current_model,
                ]));
            }

            if ($options->deleted->enable && $options->force) {
                foreach ($current_model->getLocalRelations() as $relation_name => $relation_props) {
                    if ($relation_props->type === RelationshipTypeEnum::BelongsToMany) {
                        /** @var BelongsToMany $relation */
                        $relation = $current_model->$relation_name();
                        DB::table($relation->getTable())
                            ->whereIn($relation->getForeignPivotKeyName(), [$key])
                            ->delete();
                    }
                    if ($relation_props->type === RelationshipTypeEnum::HasMany) {
                        /** @var HasMany $relation */
                        $relation = $current_model->$relation_name();
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

                $current_model->forceDelete();
            } else {
                $current_model->delete();
            }

            if ($closure) {
                $closure(new ActionClosureDataDTO([
                    'mode' => ActionClosureModeEnum::AfterDeleting,
                    'data' => $current_model,
                ]));
            }

            $this->commit();

            if ($closure) {
                $closure(new ActionClosureDataDTO([
                    'mode' => ActionClosureModeEnum::AfterCommit,
                    'data' => $current_model,
                ]));
            }

            event(new DestroyEnded(
                modelClass: $this->currentModel::class,
                data: $current_model,
            ));

            return true;
        } catch (Throwable $exception) {
            event(new DestroyError(
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
