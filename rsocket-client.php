<?php

require 'vendor/autoload.php';

use React\EventLoop\Loop;
use RSocket\Payload;
use RSocket\RSocket;
use RSocket\RSocketConnector;
use Rx\Observable;
use Rx\Scheduler;

/** @noinspection PhpUnhandledExceptionInspection */
Scheduler::setDefaultFactory(function () {
    return new Scheduler\EventLoopScheduler(Loop::get());
});

$rsocketCall = function (RSocket $rsocket) {
    $observablePayload = $rsocket->requestResponse(Payload::fromText("text/plain", "Ping"));
    $observablePayload->subscribe(
        function (Payload $payload) {
            echo 'Result: ' . $payload->getDataUtf8() . PHP_EOL;
        }
    );
};

$rsocketPromise = RSocketConnector::create()->connect("tcp://127.0.0.1:42252");
$rsocketPromise->then($rsocketCall);
$target = Observable::fromPromise($rsocketPromise);
$target->map($rsocketCall)->subscribe();

Loop::get()->run();


