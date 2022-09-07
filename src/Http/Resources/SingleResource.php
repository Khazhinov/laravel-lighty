<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\MergeValue;

abstract class SingleResource extends JsonResource
{
    /**
     * @param $resource
     * @param  mixed  $is_parent
     */
    public function __construct($resource, mixed $is_parent = false)
    {
        if (! is_bool($is_parent)) {
            $is_parent = false;
        }

        $this->is_parent = $is_parent;

        parent::__construct($resource);
    }

    /**
     * Customize the outgoing response for the resource.
     *
     * @param  Request  $request
     * @param  JsonResponse  $response
     * @return void
     */
    public function withResponse($request, $response): void
    {
        // $response->header('X-Value', 'True');
    }

    /**
     * @return MergeValue|mixed
     */
    public function withLoggingableAttributes(): mixed
    {
        return $this->merge([
            $this->mergeWhen($this->resource->created_at && $this->resource->created_by, [
                'created_at' => $this->resource->created_at,
                'created_by' => $this->resource->created_by,
            ]),
            $this->mergeWhen($this->resource->updated_at && $this->resource->updated_by, [
                'updated_at' => $this->resource->updated_at,
                'updated_by' => $this->resource->updated_by,
            ]),
            $this->mergeWhen($this->resource->deleted_at && $this->resource->deleted_by, [
                'deleted_at' => $this->resource->deleted_at,
                'deleted_by' => $this->resource->deleted_by,
            ]),
        ]);
    }
}
