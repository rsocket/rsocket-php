<?php


namespace RSocket\routing;

use RSocket\metadata\CompositeMetadata;
use RSocket\metadata\RoutingMetadata;
use RSocket\Payload;
use RSocket\RSocket;
use RSocket\utils\UTF8;
use Rx\Observable;

class RSocketServiceStub
{
    private string $serviceName;
    /**
     * @var Observable<RSocket>
     */
    private Observable $target;

    public function __construct(string $serviceName, Observable $target)
    {
        $this->serviceName = $serviceName;
        $this->target = $target;
    }

    public function __call(string $methodName, array $params): Observable
    {
        $routingKey = $this->serviceName . '.' . $methodName;
        return $this->target->flatMap(function (RSocket $rsocket) use (&$routingKey, &$params) {
            $compositeMetadata = CompositeMetadata::fromEntries(new RoutingMetadata($routingKey));
            $payloadData = null;
            if ($params !== null) {
                $payloadData = UTF8::encode(json_encode($params));
            }
            return $rsocket->requestResponse(Payload::fromArray($compositeMetadata->toUint8Array(), $payloadData))
                ->map(function (Payload $payload) use ($routingKey) {
                    return $this->decodePayloadData($payload, $routingKey);
                });
        });
    }

    public function decodePayloadData(Payload $payload, string $routingKey)
    {
        $utf8Data = $payload->getDataUtf8();
        if ($utf8Data !== null && $utf8Data !== '') {
            $firstChar = $utf8Data[0];
            // json text validate & decode
            if ($firstChar === '{' || $firstChar === '[' || $firstChar === '"') {
                $arrayObj = json_decode($utf8Data);
                $decodeHandler = JsonDecodeFactory::getHandler($routingKey);
                if ($decodeHandler !== null) {
                    $decodeHandler($arrayObj);
                }
                return $arrayObj;
            }
        }
        return $utf8Data;
    }
}