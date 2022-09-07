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
    public function respond(ApiResponseDTO $action_response, Closure $closure = null): Response
    {
        $serializer = $this->getSerializer();
        $context = new SerializationContext();
        $context->setSerializeNull(true);

        $content = $serializer->serialize($action_response->buildResponseContent(), 'json', $context);

        $response = new Response($content, $action_response->code ?: 200, $action_response->headers ?: []);

        if (! is_null($closure)) {
            $tmp_response = $closure($response);
            if ($tmp_response instanceof Response) {
                $response = $tmp_response;
            }
        }

        return $response;
    }

    public function getSerializer(): SerializerInterface
    {
        if (property_exists($this, 'serializer') && $this->serializer instanceof SerializerInterface) {
            return $this->serializer;
        }

        return SerializerBuilder::create()->build();
    }
}
