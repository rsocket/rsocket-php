<?php

require 'vendor/autoload.php';

use RSocket\AbstractRSocket;
use RSocket\CallableSocketAcceptor;
use RSocket\Payload;
use RSocket\RSocketServer;
use Rx\Observable;
use Rx\Scheduler;

$loop = React\EventLoop\Factory::create();

/** @noinspection PhpUnhandledExceptionInspection */
Scheduler::setDefaultFactory(function () use ($loop) {
    return new Scheduler\EventLoopScheduler($loop);
});

$listenUrl = "tcp://127.0.0.1:42252";
$socketAcceptor = CallableSocketAcceptor::handle(function ($setupPayload, $sendingRSocket) {
    return AbstractRSocket::requestResponseHandler(function ($payload) {
        print('Received:' . $payload->getDataUtf8());
        return Observable::of(Payload::fromText("metadata", "PONG"));
    });
});
$server = RSocketServer::create($loop, $socketAcceptor)->bind($listenUrl);
echo "RSocket Server started on ${listenUrl}\n";
$loop->run();
