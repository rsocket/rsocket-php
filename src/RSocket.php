<?php

namespace RSocket;

use Rx\Observable;
use RSocket\io\Closeable;
use RSocket\io\Availability;


interface RSocket extends Closeable, Availability
{
    public function fireAndForget(Payload $payload): Observable;

    public function requestResponse(Payload $payload): Observable;

    public function requestStream(Payload $payload): Observable;

    public function requestChannel(Observable $flux): Observable;

    public function metadataPush(Payload $payload): Observable;

}
