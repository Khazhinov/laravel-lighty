<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Http\Resources;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\MergeValue;
use Illuminate\Http\Resources\MissingValue;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @property mixed $preserveKeys
 */
abstract class JsonResource extends \Illuminate\Http\Resources\Json\JsonResource
{
    public static bool $from_collection;
    public static bool $force_is_parent = false;

    /**
     * @var array<string, array<string>>
     */
    public array $additions = [
        'properties' => [],
        'relationships' => [],
    ];

    public bool $is_parent = false;
    public bool $ignore_properties_if_parents = false;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource)
    {
        parent::__construct($resource);

        $this->resource = $resource;
    }

    /**
     * @param  bool  $condition
     * @param  Closure  $closure
     * @return MergeValue|MissingValue|mixed
     */
    public function mergeWhenByClosure(bool $condition, Closure $closure): mixed
    {
        if ($condition) {
            return $this->merge($closure($this));
        }

        return new MissingValue();
    }

    /**
     * @param  string  $key
     * @param  bool  $force_has_with
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function hasWith(string $key, bool $force_has_with = false): bool
    {
        $key_array = explode('.', $key);
        if ($key_array[0] === 'properties') {
            $this->additions['properties'][] = $key_array[1];
            if ($this->is_parent) {
                if ($this->ignore_properties_if_parents) {
                    return false;
                }

                if ($force_has_with) {
                    return $this->hasWithInRequest($key);
                }

                return true;
            }

            return $this->hasWithInRequest($key);
        }

        if ($key_array[0] === 'relationships') {
            $this->additions['relationships'][] = $key_array[1];
            if ($this->is_parent) {
                if ($force_has_with) {
                    return $this->hasWithInRequest($key);
                }

                return true;
            }

            return $this->hasWithInRequest($key) && $this->resource->relationLoaded($this->resource->completeRelation($key_array[1]));
        }

        return false;
    }

    /**
     * @param  string  $key
     * @return bool
     */
    private function hasWithInRequest(string $key): bool
    {
        /** @var Request $request */
        $request = \request();

        if ($request->has('with')) {
            $with = $request->get('with');
            $exploded_key = explode('.', $key);
            if (count($exploded_key) === 2 && array_key_exists($exploded_key[0], $with)) {
                return in_array($exploded_key[1], $with[$exploded_key[0]], true);
            }
        }

        return false;
    }

    /**
     * Create new anonymous resource collection.
     *
     * @param  mixed  $resource
     * @return AnonymousResourceCollection
     */
    public static function collection($resource): AnonymousResourceCollection
    {
        if (! self::$force_is_parent) {
            static::$from_collection = true;
        }

        return parent::collection($resource);
    }
}
