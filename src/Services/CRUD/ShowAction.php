<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Services\CRUD;

use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\ShowAction\Option\ShowActionOptionsDTO;
use Khazhinov\LaravelLighty\Models\Model;
use Throwable;

class ShowAction extends BaseCRUDAction
{
    /**
     * Функция получения одной модели по значению её PrimaryKey
     *
     * @param  ShowActionOptionsDTO  $options
     * @param  mixed  $key
     * @return Model
     * @throws Throwable
     */
    public function handle(ShowActionOptionsDTO $options, mixed $key): Model
    {
        return $this->getModelByKey(
            $options,
            $key
        );
    }
}
