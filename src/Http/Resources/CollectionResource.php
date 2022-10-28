<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Http\Resources;

use ArrayIterator;
use Countable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\PaginatedResourceResponse;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use IteratorAggregate;
use JsonSerializable;
use LogicException;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Traversable;

/**
 * @template T of object
 */
abstract class CollectionResource extends JsonResource implements Countable, IteratorAggregate
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public string $collects;

    /**
     * The mapped collection instance.
     *
     * @var Collection
     */
    public Collection $collection;

    /**
     * Indicates if all existing request query parameters should be added to pagination links.
     *
     * @var bool
     */
    protected bool $preserveAllQueryParameters = false;

    /**
     * The query parameters that should be added to the pagination links.
     *
     * @var array<string, mixed>|null
     */
    protected ?array $queryParameters = null;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @param  string|null  $single_resource_class
     */
    public function __construct($resource, string|null $single_resource_class = null)
    {
        parent::$from_collection = true;
        parent::__construct($resource);

        $this->setCollects($single_resource_class);
        $this->resource = $this->collectResource($resource);
    }

    /**
     * Map the given collection resource into its individual resources.
     *
     * @param  mixed  $resource
     * @return mixed
     */
    protected function collectResource($resource)
    {
        if ($resource instanceof MissingValue) {
            return $resource;
        }

        if (is_array($resource)) {
            $resource = new Collection($resource);
        }

        $collects = $this->collects();

        $this->collection = $collects && ! $resource->first() instanceof $collects
            ? $resource->mapInto($collects)
            : $resource->toBase();

        return ($resource instanceof AbstractPaginator || $resource instanceof AbstractCursorPaginator)
            ? $resource->setCollection($this->collection)
            : $this->collection;
    }

    /**
     * Get the resource that this resource collects.
     *
     * @return string|null
     */
    protected function collects()
    {
        $collects = null;

        if ($this->collects) {
            $collects = $this->collects;
        } elseif (str_ends_with(class_basename($this), 'Collection') &&
            (class_exists($class = Str::replaceLast('Collection', '', get_class($this))) ||
                class_exists($class = Str::replaceLast('Collection', 'Resource', get_class($this))))) {
            $collects = $class;
        }

        if (! $collects || is_a($collects, JsonResource::class, true)) {
            return $collects;
        }

        throw new LogicException('Resource collections must collect instances of '.JsonResource::class.'.');
    }

    /**
     * Get the JSON serialization options that should be applied to the resource response.
     *
     * @return int
     * @throws ReflectionException
     */
    public function jsonOptions(): int
    {
        /**
         * @var class-string<T>|T $collects
         */
        $collects = $this->collects();

        if (! $collects) {
            return 0;
        }

        $reflector = new ReflectionClass($collects);
        /** @var CollectionResource $instance */
        $instance = $reflector->newInstanceWithoutConstructor();

        return $instance->jsonOptions();
    }

    /**
     * Get an iterator for the resource collection.
     *
     * @return ArrayIterator
     */
    public function getIterator(): Traversable
    {
        return $this->collection->getIterator();
    }

    /**
     * Set collects type for collection.
     *
     * @param  string|null  $single_resource_class
     * @return void
     */
    protected function setCollects(string|null $single_resource_class): void
    {
        if (is_string($single_resource_class) && class_exists($single_resource_class)) {
            $this->collects = $single_resource_class;
        } else {
            if (str_ends_with(class_basename($this), 'Collection') &&
                (class_exists($class = Str::replaceLast('Collection', '', get_class($this))) ||
                    class_exists($class = Str::replaceLast('Collection', 'Resource', get_class($this))))) {
                $this->collects = $class;
            }
        }

        if (! $this->collects) {
            throw new RuntimeException('Cannot read single resource class');
        }
    }

    /**
     * Indicate that all current query parameters should be appended to pagination links.
     *
     * @return $this
     */
    public function preserveQuery()
    {
        $this->preserveAllQueryParameters = true;

        return $this;
    }

    /**
     * Specify the query string parameters that should be present on pagination links.
     *
     * @param  array<string, mixed>  $query
     * @return $this
     */
    public function withQuery(array $query)
    {
        $this->preserveAllQueryParameters = false;

        $this->queryParameters = $query;

        return $this;
    }

    /**
     * Return the count of items in the resource collection.
     *
     * @return int
     */
    public function count(): int
    {
        return $this->collection->count();
    }

    /**
     * Transform the resource into a JSON array.
     *
     * @param  Request  $request
     * @return array<mixed>|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        $this->collection = $this->collection->map(static function ($item, $key) use ($request) {
            return $item->toArray($request);
        });

        return $this->collection->all();
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function toResponse($request)
    {
        if ($this->resource instanceof AbstractPaginator || $this->resource instanceof AbstractCursorPaginator) {
            return $this->preparePaginatedResponse($request);
        }

        return parent::toResponse($request);
    }

    /**
     * Create a paginate-aware HTTP response.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    protected function preparePaginatedResponse($request)
    {
        if ($this->preserveAllQueryParameters) {
            $this->resource->appends($request->query());
        } elseif (! is_null($this->queryParameters)) {
            $this->resource->appends($this->queryParameters);
        }

        return (new PaginatedResourceResponse($this))->toResponse($request);
    }
}
