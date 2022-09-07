<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Khazhinov\LaravelLighty\Http\Requests\Enum;
use Khazhinov\LaravelLighty\Models\AuthenticatableModel;

if (! function_exists('array_merge_recursive_distinct')) {
    /**
     * Merges any number of arrays / parameters recursively, replacing
     * entries with string keys with values from latter arrays.
     * If the entry or the next value to be assigned is an array, then it
     * automagically treats both arguments as an array.
     * Numeric entries are appended, not replaced, but only if they are
     * unique
     *
     * @param  array<mixed>  ...$arrays
     * @return array<mixed>
     */
    function array_merge_recursive_distinct(array ...$arrays): array
    {
        $base = array_shift($arrays);
        if (! is_array($base)) {
            $base = [$base];
        }
        foreach ($arrays as $append) {
            if (! is_array($append)) {
                $append = [$append];
            }
            foreach ($append as $key => $value) {
                if (! array_key_exists($key, $base) && ! is_numeric($key)) {
                    $base[$key] = $value;

                    continue;
                }

                if ((isset($base[$key]) && is_array($base[$key])) || is_array($value)) {
                    $base[$key] = array_merge_recursive_distinct($base[$key], $value);
                } else {
                    if (is_numeric($key)) {
                        if (! in_array($value, $base, true)) {
                            $base[] = $value;
                        }
                    } else {
                        $base[$key] = $value;
                    }
                }
            }
        }

        return $base;
    }
}

if (! function_exists('get_user')) {
    /**
     * Get a guard instance by name.
     *
     * @param  string  $guard
     * @return AuthenticatableModel|null
     */
    function get_user(string $guard = 'api'): AuthenticatableModel|null
    {
        /** @var Factory $auth */
        $auth = auth();
        $user = $auth->guard($guard)->user();

        if ($user instanceof AuthenticatableModel) {
            return $user;
        }

        return null;
    }
}

if (! function_exists('helper_array_get')) {
    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  array<mixed>|ArrayAccess  $array
     * @param  string|int|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    function helper_array_get(array|ArrayAccess $array, string|int|null $key, mixed $default = null): mixed
    {
        return \Illuminate\Support\Arr::get($array, $key, $default);
    }
}

if (! function_exists('helper_array_set')) {
    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param  array<mixed>|ArrayAccess $array
     * @param  string|null  $key
     * @param  mixed  $value
     * @return array<mixed>
     */
    function helper_array_set(array|ArrayAccess $array, string|null $key, mixed $value): array
    {
        return \Illuminate\Support\Arr::set($array, $key, $value);
    }
}

if (! function_exists('helper_array_has')) {
    /**
     * Check if an item or items exist in an array using "dot" notation.
     *
     * @param  array<mixed>|ArrayAccess  $array
     * @param string $key
     * @return bool
     */
    function helper_array_has(array|ArrayAccess $array, string $key): bool
    {
        return \Illuminate\Support\Arr::has($array, $key);
    }
}

if (! function_exists('helper_array_unset')) {
    /**
     * @param  array<int|string, mixed>  $array
     * @param string|int $index
     * @return array<int|string, mixed>
     */
    function helper_array_unset(array $array, string|int $index): array
    {
        unset($array[$index]);
        $tmp_array = [];
        foreach ($array as $item) {
            $tmp_array[] = $item;
        }

        return $tmp_array;
    }
}

if (! function_exists('convert_object_to_array')) {
    /**
     * Convert object to array.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param object|array<mixed>|iterable<TKey, TValue> $object
     * @return array<mixed>
     */
    function convert_object_to_array(object|array $object): array
    {
        if (is_array($object)) {
            return $object;
        }

        if ($object instanceof \Khazhinov\LaravelLighty\Models\Model) {
            return $object->getAttributes();
        }

        /** @var iterable<TKey, TValue> $object */

        return (new \Illuminate\Support\Fluent($object))->toArray();
    }
}

if (! function_exists('helper_is_assoc_array')) {
    /**
     * Check if array is associative
     *
     * @param array<mixed> $array
     * @return bool
     */
    function helper_is_assoc_array(array $array): bool
    {
        return ! array_is_list($array);
    }
}

if (! function_exists('helper_string_snake')) {
    /**
     * To snake_case
     *
     * @param string $value
     * @return string
     */
    function helper_string_snake(string $value): string
    {
        return \Illuminate\Support\Str::snake($value);
    }
}

if (! function_exists('helper_string_title')) {
    /**
     * To Title Case
     *
     * @param string $value
     * @return string
     */
    function helper_string_title(string $value): string
    {
        $snaked = helper_string_snake($value);
        $title = str_replace('_', ' ', $snaked);

        return \Illuminate\Support\Str::title($title);
    }
}

if (! function_exists('helper_string_plural')) {
    /**
     * Converts a singular word string to its plural form.
     *
     * @param  string $string
     * @return string
     */
    function helper_string_plural(string $string): string
    {
        return \Illuminate\Support\Str::plural($string);
    }
}

if (! function_exists('get_controller_validation_request_classes')) {
    /**
     * @param string|object $controller_class
     * @return array<int|string, string[]>
     * @throws ReflectionException
     * @throws Exception
     */
    function get_controller_validation_request_classes(string|object $controller_class): array
    {
        if (is_object($controller_class)) {
            if (! ($controller_class instanceof Khazhinov\LaravelLighty\Http\Controllers\Controller)) {
                throw new RuntimeException('[ControllerValidatorsCollector] Переданный объект не унаследован от \App\Http\Controllers\Common\Controller');
            }
            $controller_class = get_class($controller_class);
        } elseif (is_string($controller_class)) {
            if (! str_contains($controller_class, 'App\Http\Controllers')) {
                throw new RuntimeException('[ControllerValidatorsCollector] Неверный аргумент класса контроллера');
            }
        } else {
            throw new RuntimeException('[ControllerValidatorsCollector] Неверный аргумент класса контроллера');
        }
        $compiled_methods = [];
        $methods = get_class_methods($controller_class);

        foreach ($methods as $method) {
            $tmp = new ReflectionMethod($controller_class, $method);
            $compiled_methods[$method] = $tmp->getParameters();
        }
        $result = [];

        foreach ($compiled_methods as $method => $signature) {
            foreach ($signature as $parameter) {
                if ($parameter instanceof ReflectionParameter) {
                    if ($parameter_class = $parameter->getType()) {
                        /** @var ReflectionNamedType $parameter_class */
                        if ($parameter_class_name = $parameter_class->getName()) {
                            try {
                                $result[$method] = generate_doc_request_by_request_class($parameter_class_name);
                            } catch (\Throwable $exception) {
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }
}
if (! function_exists('generate_doc_request_by_request_class')) {
    /**
     * @param  string|object  $request_class
     * @return string[]
     * @throws Exception
     */
    function generate_doc_request_by_request_class(string|object $request_class): array
    {
        if (! ($request_class instanceof Khazhinov\LaravelLighty\Http\Requests\BaseRequest)) {
            $request = new $request_class();
        }

        if (! isset($request) || ! ($request instanceof Khazhinov\LaravelLighty\Http\Requests\BaseRequest)) {
            throw new RuntimeException('[DocRequestGenerator] Переданный класс не унаследован от Khazhinov\LaravelLighty\Http\Requests\BaseRequest');
        }

        /** @var Khazhinov\LaravelLighty\Http\Requests\BaseRequest $request */
        $request_rules = $request->rules();
        $result_rules = [];

        foreach ($request_rules as $rule_name => $rule_validations) {
            $tmp = "";
            if (is_array($rule_validations)) {
                $tmp_rule_validation = [];
                foreach ($rule_validations as $validation) {
                    if ($validation instanceof Enum) {
                        $enum_class = $validation->type;
                        if (function_exists('enum_exists') && enum_exists($enum_class) && method_exists(
                            $enum_class,
                            'cases'
                        )) {
                            /**
                             * @var array<string, mixed>|Arrayable $enum_cases
                             */
                            $enum_cases = $enum_class::cases();

                            $tmp_rule_validation[] = "in:" . implode(',', collect($enum_cases)->pluck('value')->toArray());
                        }
                    } else {
                        $tmp_rule_validation[] = $validation;
                    }
                }
                $tmp .= implode('|', $tmp_rule_validation);
            } else {
                $tmp .= (string) $rule_validations;
            }
            $result_rules[$rule_name] = str_replace('"', "'", $tmp);
        }

        return $result_rules;
    }
}

if (! function_exists('generate_url')) {
    /**
     * Generate URL string
     *
     * @param  string  $base
     * @param  string  $path
     * @param  array<string, mixed>  $query_params
     * @param  bool  $last_slash
     * @return string
     */
    function generate_url(string $base, string $path = '', array $query_params = [], bool $last_slash = false): string
    {
        /** @var string $url */
        $url = url($base);
        if ($path === '') {
            if (str_ends_with($url, '/')) {
                $url = substr($url, 0, -1);
            }
        } elseif (! str_ends_with($url, '/')) {
            $url .= '/';
        }

        if ($path !== '') {
            if (str_starts_with($path, '/')) {
                $path = substr($path, 1, strlen($path));
            }

            $url .= $path;
        }

        if (count($query_params)) {
            $url .= '?' . http_build_query($query_params);
        }

        if ($last_slash) {
            if (! str_ends_with($url, '/')) {
                $url .= '/';
            }
        } elseif (str_ends_with($url, '/')) {
            $url = substr($url, 0, -1);
        }

        return $url;
    }
}

if (! function_exists("sort_collection_natural")) {
    /**
     * @template TKey of array-key
     * @template TValue
     * @param  Collection<TKey, TValue>  $collection
     * @param  string  $column
     * @return Collection
     */
    function sort_collection_natural(Collection $collection, string $column): Collection
    {
        $array = $collection->sortBy($column, SORT_NATURAL);
        /** @var Arrayable<TKey, TValue>|iterable<TKey, TValue>|null $tmp */
        $tmp = null;
        $result = collect($tmp);

        foreach ($array as $item) {
            $result = $result->push($item);
        }

        return  $result;
    }
}
