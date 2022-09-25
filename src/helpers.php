<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Khazhinov\LaravelLighty\Http\Requests\Enum;
use Khazhinov\LaravelLighty\Models\AuthenticatableModel;

if (! function_exists('get_user')) {
    /**
     * Get a guard instance by name.
     *
     * @param  string  $guard
     * @return AuthenticatableModel|null
     */
    function get_user(string $guard = 'api'): AuthenticatableModel|null
    {
        try {
            /** @var Factory $auth */
            $auth = auth();
            $user = $auth->guard($guard)->user();

            if ($user instanceof AuthenticatableModel) {
                return $user;
            }
        } catch (Throwable) {
            return null;
        }

        return null;
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
