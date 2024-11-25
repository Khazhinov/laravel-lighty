<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Exceptions;

use Illuminate\Http\Request;
use JsonException;
use Khazhinov\LaravelLighty\Http\Controllers\Api\DTO\ApiResponseDTO;
use Khazhinov\LaravelLighty\Http\Respond\Respondable;
use Khazhinov\LaravelLighty\Http\Respond\RespondableInterface;
use ReflectionException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

abstract class JsonExceptionHandler implements RespondableInterface
{
    use Respondable;

    public int $json_flags = JSON_UNESCAPED_SLASHES ^ JSON_UNESCAPED_UNICODE ^ JSON_THROW_ON_ERROR;

    /**
     * @var array|string[]
     */
    public array $ignore_trace = [
        \Illuminate\Validation\ValidationException::class,
        \Illuminate\Auth\AuthenticationException::class,
    ];

    /**
     * @throws ReflectionException
     * @throws UnknownProperties
     * @throws JsonException
     */
    protected function jsonRender(Request $request, \Throwable $exception): Response
    {
        $error_data = $exception->getMessage();

        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            $error_data = $exception->errors();
        }

        $error_trace = null;

        if (config('app.debug')) {
            $is_need_trace = true;
            foreach ($this->ignore_trace as $ignore_trace_exception_class) {
                if ($exception instanceof $ignore_trace_exception_class) {
                    $is_need_trace = false;
                }
            }

            if ($is_need_trace) {
                $error_trace = json_decode(
                    json: json_encode(
                        value: $exception->getTrace(),
                        flags: $this->json_flags,
                    ),
                    associative: true,
                    flags: $this->json_flags,
                );
            }
        }

        return $this->respond(
            action_response: $this->buildActionResponseDTO($exception, $error_data, $error_trace),
            json_flags: $this->json_flags,
        );
    }

    /**
     * @throws ReflectionException
     * @throws UnknownProperties
     */
    protected function buildActionResponseDTO(
        Throwable $exception,
        mixed $error_data,
        mixed $error_trace,
    ): ApiResponseDTO {
        $code = Response::HTTP_BAD_REQUEST;

        $response = [
            'status' => 'error',
            'code' => $this->normalizeStatusCode($exception->getCode()),
            'message' => Response::$statusTexts[$code] ?? 'Something went wrong..',
            'error' => $error_data,
        ];

        if (! is_null($error_trace)) {
            $response['meta'] = [
                'trace' => $error_trace,
            ];
        }

        return new ApiResponseDTO($response);
    }
}
