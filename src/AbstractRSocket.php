<?php

namespace RSocket;

use RSocket\core\RSocketException;
use Rx\Observable;

class AbstractRSocket implements RSocket
{

    public function fireAndForget(Payload $payload): Observable
    {
        return Observable::empty();
    }

    public function requestResponse(Payload $payload): Observable
    {
        return Observable::error(new RSocketException("Unsupported"));
    }

    public function requestStream(Payload $payload): Observable
    {
        return Observable::error(new RSocketException("Unsupported"));
    }

    public function requestChannel(Observable $flux): Observable
    {
        return Observable::error(new RSocketException("Unsupported"));
    }

    public function metadataPush(Payload $payload): Observable
    {
        return Observable::empty();
    }

    public function close(): void
    {

    }

    public function availability(): float
    {
        return 0.0;
    }

    public static function requestResponseHandler($callable): RSocket
    {
        return new class($callable) extends AbstractRSocket {
            private $handler;

            public function __construct(callable $handler)
            {
                $this->handler = $handler;
            }

            public function requestResponse(Payload $payload): Observable
            {
                return ($this->handler)($payload);
            }
        };
    }
}