<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Http\Respond;

use Closure;
use JsonException;
use Khazhinov\LaravelLighty\Http\Controllers\Api\DTO\ApiResponseDTO;
use Symfony\Component\HttpFoundation\Response;

trait Respondable
{
    /**
     * @throws JsonException
     */
    public function respond(
        ApiResponseDTO $action_response,
        Closure $closure = null,
        int $json_flags = JSON_UNESCAPED_SLASHES ^ JSON_UNESCAPED_UNICODE ^ JSON_THROW_ON_ERROR
    ): Response {
        $content = json_encode($action_response->buildResponseContent(), $json_flags);

        $response = new Response(
            $content,
            $this->normalizeStatusCode($action_response->code),
            $action_response->headers ?: []
        );

        if (! is_null($closure)) {
            $tmp_response = $closure($response);
            if ($tmp_response instanceof Response) {
                $response = $tmp_response;
            }
        }

        return $response;
    }

    protected function normalizeStatusCode(mixed $status_code): int
    {
        if (array_key_exists($status_code, Response::$statusTexts)) {
            return $status_code;
        }

        return 400;
    }
}
