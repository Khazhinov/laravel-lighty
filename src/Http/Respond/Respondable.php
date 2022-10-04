<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Http\Respond;

use Closure;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use Khazhinov\LaravelLighty\Http\Controllers\Api\DTO\ApiResponseDTO;
use Symfony\Component\HttpFoundation\Response;

trait Respondable
{
    /**
     * @throws \JsonException
     */
    public function respond(ApiResponseDTO $action_response, Closure $closure = null): Response
    {
//        $serializer = $this->getSerializer();
//        $context = new SerializationContext();
//        $context->setSerializeNull(true);

        $content = json_encode($action_response->buildResponseContent(), JSON_THROW_ON_ERROR);
//        $content = $serializer->serialize($action_response->buildResponseContent(), 'json', $context);

        $response = new Response($content, $this->normalizeStatusCode($action_response->code), $action_response->headers ?: []);

        if (! is_null($closure)) {
            $tmp_response = $closure($response);
            if ($tmp_response instanceof Response) {
                $response = $tmp_response;
            }
        }

        return $response;
    }

    protected function normalizeStatusCode(int $status_code): int
    {
        if (array_key_exists($status_code, Response::$statusTexts)) {
            return $status_code;
        }

        return 400;
    }

    public function getSerializer(): SerializerInterface
    {
        if (property_exists($this, 'serializer') && $this->serializer instanceof SerializerInterface) {
            return $this->serializer;
        }

        return SerializerBuilder::create()->build();
    }
}
