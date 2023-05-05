<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Services\CRUD;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\ActionClosureModeEnum;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\ActionOptionsRelationships;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Option\IndexActionOptionsDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Payload\IndexActionRequestPayloadDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Payload\IndexActionRequestPayloadFilterBooleanEnum;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Payload\IndexActionRequestPayloadFilterDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Payload\IndexActionRequestPayloadFilterTypeEnum;
use Khazhinov\LaravelLighty\Services\CRUD\DTO\ActionClosureDataDTO;
use Khazhinov\LaravelLighty\Services\CRUD\Exceptions\ColumnMustBeSpecifiedException;
use ReflectionException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class IndexAction extends BaseCRUDAction
{
    /**
     * Универсальный метод поиска сущностей
     *
     * @param  Builder|DatabaseBuilder  $builder
     * @param  IndexActionOptionsDTO  $options
     * @param  IndexActionRequestPayloadDTO  $data
     * @param  array<string>  $orders
     * @param  Closure|null  $closure
     * @return mixed
     * @throws ReflectionException
     * @throws UnknownProperties
     */
    public function handle(Builder|DatabaseBuilder $builder, IndexActionOptionsDTO $options, IndexActionRequestPayloadDTO $data, array $orders = [], Closure $closure = null): mixed
    {
        $builder = $this->getPreparedQueryBuilder($builder, $options);

        if ($options->filters->enable) {
            $builder = $this->addFilters($options, $data->filter, $builder);
        }

        if ($options->orders->enable) {
            $builder = $this->addOrders($options, $data, $builder, $orders);
        }

        if ($options->relationships->enable) {
            $builder = $this->addRelationships($options->relationships, $data, $builder);
        }

        if ($closure) {
            $tmp_builder = $closure(new ActionClosureDataDTO([
                'mode' => ActionClosureModeEnum::Builder,
                'data' => $builder,
            ]));
            if ($tmp_builder) {
                $builder = $tmp_builder;
            }
        }

        if ($options->pagination->enable) {
            $limit = $data->limit;
            $page = $data->page;
            $items = $builder->paginate($limit, page: $page);
        } else {
            $items = $builder->get();
        }

        if ($closure && $filter_result = $closure(new ActionClosureDataDTO([
                'mode' => ActionClosureModeEnum::Filter,
                'data' => $items,
            ]))) {
            $items = $filter_result;
        }

        return $items;
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
            throw new ColumnMustBeSpecifiedException();
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
     * @param  IndexActionOptionsDTO  $options
     * @param  IndexActionRequestPayloadDTO  $request
     * @param  Builder|DatabaseBuilder  $builder
     * @param  array<string>  $default_orders
     * @return Builder|DatabaseBuilder
     */
    protected function addOrders(IndexActionOptionsDTO $options, IndexActionRequestPayloadDTO $request, Builder|DatabaseBuilder $builder, array $default_orders): Builder|DatabaseBuilder
    {
        $orders = $request->order;

        if (! $orders) {
            $orders = $default_orders;
        }

        foreach ($orders as $order) {
            $builder = $this->addOrder($options, $builder, htmlspecialchars($order, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        }

        return $builder;
    }

    /**
     * @param  IndexActionOptionsDTO  $options
     * @param  Builder|DatabaseBuilder  $builder
     * @param  string  $order
     * @return Builder|DatabaseBuilder
     */
    protected function addOrder(IndexActionOptionsDTO $options, Builder|DatabaseBuilder $builder, string $order): Builder|DatabaseBuilder
    {
        $direction = 'asc';

        if (str_starts_with($order, '-')) {
            $direction = 'desc';
            $order = substr($order, 1);
        }

        if ($options->orders->null_control) {
            $builder->orderByRaw(sprintf('? %s NULLS %s', $direction, $options->orders->null_position->value), [$order]);
        } else {
            $builder->orderBy($order, $direction);
        }

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
        } elseif ($this->checkRelationship($relationship)) {
            $builder = $builder->with($relationship);
        }

        return $builder;
    }
}
