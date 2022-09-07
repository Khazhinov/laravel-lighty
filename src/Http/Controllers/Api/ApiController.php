<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Http\Controllers\Api;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonException;
use Khazhinov\LaravelLighty\Exceptions\Http\ActionResponseException;
use Khazhinov\LaravelLighty\Http\Controllers\Api\DTO\ApiResponseDTO;
use Khazhinov\LaravelLighty\Http\Controllers\Controller;
use Khazhinov\LaravelLighty\Http\Respond\Respondable;
use Khazhinov\LaravelLighty\Http\Respond\RespondableInterface;
use ReflectionException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

abstract class ApiController extends Controller implements RespondableInterface
{
    use Respondable;

    /**
     * @param  string|null  $method
     * @return Response
     * @throws JsonException
     * @throws ReflectionException
     * @throws UnknownProperties
     */
    public function getValidations(string|null $method = null): Response
    {
        $validations = get_controller_validation_request_classes($this);

        /** @var ResponseFactory $response */
        $response = response();

        if (is_null($method)) {
            return $this->respond(
                $this->buildActionResponseDTO(
                    data: $validations,
                )
            );
        }

        if (array_key_exists($method, $validations)) {
            return $this->respond(
                $this->buildActionResponseDTO(
                    data: [
                        $method => $validations[$method],
                    ],
                )
            );
        }

        return $this->respond(
            $this->buildActionResponseDTO(
                data: null,
            )
        );
    }

    /**
     * @throws UnknownProperties
     * @throws JsonException
     * @throws ReflectionException
     */
    public function buildNotFoundResponse(): Response
    {
        return $this->respond(
            $this->buildActionResponseDTO(
                data: "Not Found",
                status: 'error',
                code: Response::HTTP_NOT_FOUND,
            )
        );
    }

    /**
     * @param  mixed  $data
     * @param  mixed|null  $meta
     * @param  string  $status
     * @param  int  $code
     * @param  string|null  $message
     * @param  array<string, string>  $headers
     * @return ApiResponseDTO
     * @throws UnknownProperties
     * @throws JsonException|ReflectionException
     */
    public function buildActionResponseDTO(
        mixed $data,
        mixed $meta = null,
        string $status = 'success',
        int $code = Response::HTTP_OK,
        ?string $message = null,
        array $headers = ['Content-Type' => 'application/json'],
    ): ApiResponseDTO {
        if ($code === Response::HTTP_OK && $data instanceof Throwable) {
            $code = Response::HTTP_BAD_REQUEST;
        }

        if (! in_array('Content-Type', $headers, true)) {
            $headers['Content-Type'] = 'application/json';
        }

        if ($data instanceof JsonResource) {
            $tmp_data = json_decode(
                $data->toResponse($this->request)->content(),
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            if (array_key_exists('data', $tmp_data)) {
                $data = $tmp_data['data'];
            }

            if (! $meta && array_key_exists('meta', $tmp_data)) {
                $meta = $tmp_data['meta'];
            }
        }

        $response = [
            'status' => $status,
            'code' => $code,
            'message' => $message ?: Response::$statusTexts[$code],
            'headers' => $headers,
            'meta' => $meta,
        ];

        if ($status === 'success') {
            $response['data'] = $data;
        } elseif ($status === 'error' || $data instanceof Throwable) {
            $response['error'] = $data;
        } else {
            throw new ActionResponseException('Unknown status code');
        }

        return new ApiResponseDTO($response);
    }
}
