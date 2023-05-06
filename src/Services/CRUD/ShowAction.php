<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Services\CRUD;

use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\ShowAction\Option\ShowActionOptionsDTO;
use Khazhinov\LaravelLighty\Models\Model;
use Khazhinov\LaravelLighty\Services\CRUD\Events\Show\ShowCalled;
use Khazhinov\LaravelLighty\Services\CRUD\Events\Show\ShowEnded;
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
        event(new ShowCalled(
            modelClass: $this->currentModel::class,
            data: $key,
        ));

        $result = $this->getModelByKey(
            $options,
            $key
        );

        event(new ShowEnded(
            modelClass: $this->currentModel::class,
            data: $key,
        ));

        return $result;
    }
}
