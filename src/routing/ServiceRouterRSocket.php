<?php


namespace RSocket\routing;


use RSocket\AbstractRSocket;
use RSocket\core\RSocketException;
use RSocket\metadata\CompositeMetadata;
use RSocket\metadata\RoutingMetadata;
use RSocket\Payload;
use RSocket\utils\UTF8;
use Rx\Observable;

class ServiceRouterRSocket extends AbstractRSocket
{
    private RSocketServiceRouter $router;

    public function __construct(RSocketServiceRouter $router)
    {
        $this->router = $router;
    }

    public function fireAndForget(Payload $payload): Observable
    {
        $metadataArray = CompositeMetadata::fromU8Array($payload->metadata)->getAllEntriesArray();
        $routingMetadata = RoutingMetadata::fromEntry($metadataArray['message/x.rsocket.routing.v0']);
        $routingParts = $routingMetadata->getRoutingParts();
        if (array_key_exists('service', $routingParts)) {
            $params = $this->decodeDataAsParams($payload, $routingMetadata->routingKey);
            if ($this->router->isServiceAvailable($routingParts['service'])) {
                $this->router->invoke($routingParts['service'], $routingParts['method'], ...$params)->subscribe();
            }
        }
        return Observable::empty();
    }

    public function requestResponse(Payload $payload): Observable
    {
        $metadataArray = CompositeMetadata::fromU8Array($payload->metadata)->getAllEntriesArray();
        $routingMetadata = RoutingMetadata::fromEntry($metadataArray['message/x.rsocket.routing.v0']);
        $routingParts = $routingMetadata->getRoutingParts();
        if (array_key_exists('service', $routingParts)) {
            $params = $this->decodeDataAsParams($payload, $routingMetadata->routingKey);
            if ($this->router->isServiceAvailable($routingParts['service'])) {
                return $this->router->invoke($routingParts['service'], $routingParts['method'], ...$params)
                    ->map(function ($result) {
                        return Payload::fromArray(null, $this->encodeResult($result));
                    });
            }
        }
        return Observable::error(new RSocketException("Service not found", 0x00000204));
    }

    public function requestStream(Payload $payload): Observable
    {
        $metadataArray = CompositeMetadata::fromU8Array($payload->metadata)->getAllEntriesArray();
        $routingMetadata = RoutingMetadata::fromEntry($metadataArray['message/x.rsocket.routing.v0']);
        $routingParts = $routingMetadata->getRoutingParts();
        if (array_key_exists('service', $routingParts)) {
            $params = $this->decodeDataAsParams($payload, $routingMetadata->routingKey);
            if ($this->router->isServiceAvailable($routingParts['service'])) {
                return $this->router->invoke($routingParts['service'], $routingParts['method'], ...$params)
                    ->map(function ($result) {
                        return Payload::fromArray(null, $this->encodeResult($result));
                    });
            }
        }
        return Observable::error(new RSocketException("Service not found", 0x00000204));
    }

    public function decodeDataAsParams(Payload $payload, string $routingKey): array
    {
        $obj = JsonDecodeFactory::decodeUtf8Text($payload->getDataUtf8(), $routingKey);
        if ($obj === null) {
            return [];
        }
        if (is_array($obj)) {
            return $obj;
        }
        return [$obj];
    }

    public function encodeResult($obj): array
    {
        if ($obj !== null) {
            if (is_string($obj)) {
                return UTF8::encode($obj);
            }
            // json text validate & decode
            if (is_array($obj) || is_object($obj)) {
                return UTF8::encode(json_encode($obj));
            }

        }
        return [];
    }

}