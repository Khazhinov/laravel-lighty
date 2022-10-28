<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    /**
     * Current request.
     *
     * @var Request
     */
    protected Request $request;

    /**
     * Controller action type: index, show, store, update or destroy.
     *
     * @var string
     */
    protected string $current_action;

    /**
     * Array of options. Meta information for controller actions.
     *
     * @var array<string, mixed>
     */
    protected array $options = [];

    public function __construct()
    {
        $this->request = \request();
    }

    /**
     * Set option.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return array<mixed>
     */
    protected function setOption(string $key, mixed $value): array
    {
        return helper_array_set($this->options, $key, $value);
    }

    /**
     * Get options.
     *
     * @return array<string, mixed>
     */
    protected function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Set options.
     *
     * @param  array<string, mixed>  $options
     */
    protected function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * Get option.
     *
     * @param  string  $key
     * @return mixed
     */
    protected function getOption(string $key): mixed
    {
        $result = helper_array_get($this->options, $key);

        return $result ?? false;
    }

    /**
     * Set controller action type.
     *
     * @param  string  $current_action
     */
    protected function setCurrentAction(string $current_action): void
    {
        $this->current_action = $current_action;
    }
}
