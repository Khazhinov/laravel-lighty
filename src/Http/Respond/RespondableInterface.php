<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Http\Respond;

use Closure;
use Khazhinov\LaravelLighty\Http\Controllers\Api\DTO\ApiResponseDTO;
use Symfony\Component\HttpFoundation\Response;

interface RespondableInterface
{
    public function respond(ApiResponseDTO $action_response, Closure $closure = null): Response;
}
