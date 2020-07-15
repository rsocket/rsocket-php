<?php

require 'vendor/autoload.php';

use RSocket\Payload;
use RSocket\RSocket;
use RSocket\RSocketConnector;
use Rx\Scheduler;

$loop = React\EventLoop\Factory::create();

/** @noinspection PhpUnhandledExceptionInspection */
Scheduler::setDefaultFactory(function () use ($loop) {
    return new Scheduler\EventLoopScheduler($loop);
});

$rsocketPromise = RSocketConnector::create($loop)->connect("tcp://127.0.0.1:42252");

$rsocketPromise->then(function (RSocket $rsocket) {
    $observablePayload = $rsocket->requestResponse(Payload::fromText("text/plain", "Ping"));
    $observablePayload->subscribe(
        function (Payload $x) {
            echo 'Result: ' . $x->getDataUtf8();
        }
    );
});

$loop->run();


