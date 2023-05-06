<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Services\CRUD;

use Illuminate\Support\Facades\DB;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\SetPositionAction\Option\SetPositionActionOptionsDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\SetPositionAction\Payload\SetPositionActionRequestPayloadDTO;
use Throwable;

class SetPositionAction extends BaseCRUDAction
{
    /**
     * Метод массовой установки позиции по упорядоченному списку ID
     *
     * @throws Throwable
     */
    public function handle(SetPositionActionOptionsDTO $options, SetPositionActionRequestPayloadDTO $data): bool
    {
        $this->beginTransaction();

        try {
            $table = $this->currentModel->getTable();
            $column = $options->position_column;
            $primary_column = $this->currentModel->getKeyName();
            $case = sprintf('CASE %s', $primary_column);
            foreach ($data->ids as $i => $id) {
                $case .= " WHEN ? THEN $i";
            }
            $case .= ' END';
            $ids = implode(',', array_fill(0, count($data->ids), '?'));
            $raw = sprintf('update %s set %s = %s where %s in (%s)', $table, $column, $case, $primary_column, $ids);
            $bindings = [];
            for ($i = 0; $i < 2; $i++) {
                foreach ($data->ids as $id) {
                    $bindings[] = $id;
                }
            }

            DB::update($raw, $bindings);
            $this->commit();

            return true;
        } catch (Throwable $exception) {
            $this->rollback();

            throw $exception;
        }
    }
}
