<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Http\Controllers\Api\DTO;

use Khazhinov\LaravelLighty\DTO\DataTransferObject;
use Khazhinov\LaravelLighty\Exceptions\Http\ActionResponseException;

class ApiResponseDTO extends DataTransferObject
{
    /**
     * @var string
     */
    public string $status;

    /**
     * @var int
     */
    public int $code;

    /**
     * @var string
     */
    public string $message;

    public mixed $data;

    public mixed $error;

    public mixed $meta = null;

    /**
     * @var array<string, string>
     */
    public array $headers;

    /**
     * @return array<string, mixed>
     */
    public function buildResponseContent(): array
    {
        $response = [
            'status' => $this->status,
            'code' => $this->code,
            'message' => $this->message,
        ];

        switch ($this->status) {
            case 'success':
                $response['data'] = $this->data;

                break;
            case 'error':
                $response['error'] = $this->error;

                break;
            default:
                throw new ActionResponseException('Unknown status code');
        }

        if ($this->meta) {
            $response['meta'] = $this->meta;
        }

        return $response;
    }
}
