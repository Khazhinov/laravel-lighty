<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Services\CRUD;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\ActionClosureModeEnum;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\BulkDestroyAction\Option\BulkDestroyActionOptionsDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\BulkDestroyAction\Payload\BulkDestroyActionRequestPayloadDTO;
use Khazhinov\LaravelLighty\Models\Attributes\Relationships\RelationshipTypeEnum;
use Khazhinov\LaravelLighty\Services\CRUD\DTO\ActionClosureDataDTO;
use Khazhinov\LaravelLighty\Services\CRUD\Events\BulkDestroy\BulkDestroyCalled;
use Khazhinov\LaravelLighty\Services\CRUD\Events\BulkDestroy\BulkDestroyEnded;
use Khazhinov\LaravelLighty\Services\CRUD\Events\BulkDestroy\BulkDestroyError;
use ReflectionException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Throwable;

class BulkDestroyAction extends BaseCRUDAction
{
    /**
     * Метод массового удаления
     *
     * @param  BulkDestroyActionOptionsDTO  $options
     * @param  BulkDestroyActionRequestPayloadDTO  $data
     * @param  Closure|null  $closure
     * @return bool
     * @throws Throwable
     * @throws UnknownProperties
     * @throws ReflectionException
     */
    public function handle(BulkDestroyActionOptionsDTO $options, BulkDestroyActionRequestPayloadDTO $data, Closure $closure = null): bool
    {
        event(new BulkDestroyCalled(
            modelClass: $this->currentModel::class,
            data: $data,
        ));

        $this->beginTransaction();

        try {
            if ($closure) {
                $closure(new ActionClosureDataDTO([
                    'mode' => ActionClosureModeEnum::BeforeDeleting,
                    'data' => $data,
                ]));
            }

            if ($options->force) {
                foreach ($this->currentModel->getLocalRelations() as $relation_name => $relation_props) {
                    if ($relation_props->type === RelationshipTypeEnum::BelongsToMany) {
                        /** @var BelongsToMany $relation */
                        $relation = $this->currentModel->$relation_name();
                        DB::table($relation->getTable())
                            ->whereIn(
                                $relation->getForeignPivotKeyName(),
                                $data->ids
                            )
                            ->delete();
                    }
                    if ($relation_props->type === RelationshipTypeEnum::HasMany) {
                        /** @var HasMany $relation */
                        $relation = $this->currentModel->$relation_name();
                        $tmp_key = $relation->getExistenceCompareKey();
                        /** @var int $dot_position */
                        $dot_position = mb_stripos($tmp_key, '.');
                        $table = mb_substr($tmp_key, 0, $dot_position);
                        $foreign_key = mb_substr($tmp_key, $dot_position + 1, mb_strlen($tmp_key));
                        DB::table($table)
                            ->whereIn($foreign_key, $data->ids)
                            ->update([
                                $foreign_key => null,
                            ]);
                    }
                }

                // Получаем очищенный от SoftDelete builder с целью явного удаления
                $builder = $this->currentModel::query();
                /** @var Builder $builder */
                $builder = $builder->whereIn($this->currentModel->getKey(), $data->ids);

                $builder->forceDelete();
            } else {
                $this->currentModel::destroy($data->ids);
            }

            if ($closure) {
                $closure(new ActionClosureDataDTO([
                    'mode' => ActionClosureModeEnum::AfterDeleting,
                    'data' => $data,
                ]));
            }

            $this->commit();

            if ($closure) {
                $closure(new ActionClosureDataDTO([
                    'mode' => ActionClosureModeEnum::AfterCommit,
                    'data' => $data,
                ]));
            }

            event(new BulkDestroyEnded(
                modelClass: $this->currentModel::class,
                data: $data,
            ));

            return true;
        } catch (Throwable $exception) {
            event(new BulkDestroyError(
                modelClass: $this->currentModel::class,
                data: $data,
                exception: $exception
            ));

            if ($closure) {
                $closure(new ActionClosureDataDTO([
                    'mode' => ActionClosureModeEnum::BeforeRollback,
                    'data' => $data,
                    'exception' => $exception,
                ]));
            }

            $this->rollback();

            if ($closure) {
                $closure(new ActionClosureDataDTO([
                    'mode' => ActionClosureModeEnum::AfterRollback,
                    'data' => $data,
                    'exception' => $exception,
                ]));
            }

            throw $exception;
        }
    }
}
