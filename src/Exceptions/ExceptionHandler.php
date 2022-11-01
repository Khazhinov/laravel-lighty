<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Exceptions;

use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use JsonException;
use Khazhinov\LaravelLighty\Http\Controllers\Api\DTO\ApiResponseDTO;
use Khazhinov\LaravelLighty\Http\Respond\Respondable;
use Khazhinov\LaravelLighty\Http\Respond\RespondableInterface;
use ReflectionException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

abstract class ExceptionHandler extends Handler implements RespondableInterface
{
    use Respondable;

    public function render(
        $request,
        \Throwable $e
    ): \Illuminate\Http\Response|JsonResponse|\Symfony\Component\HttpFoundation\Response {
        return $this->jsonRender($request, $e);
    }

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
                $error_trace = json_decode(json_encode($exception->getTrace(), JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
            }
        }

        return $this->respond(
            $this->buildActionResponseDTO($exception, $error_data, $error_trace)
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

        $headers = ['Content-Type' => 'application/json'];

        $response = [
            'status' => 'error',
            'code' => $this->normalizeStatusCode($exception->getCode()),
            'message' => Response::$statusTexts[$code] ?? 'Something went wrong..',
            'headers' => $headers,
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
