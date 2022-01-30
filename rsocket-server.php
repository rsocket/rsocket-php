<?php
/** @noinspection PhpUnusedParameterInspection */

require 'vendor/autoload.php';

use React\EventLoop\Loop;
use RSocket\AbstractRSocket;
use RSocket\CallableSocketAcceptor;
use RSocket\Payload;
use RSocket\RSocketServer;
use Rx\Observable;
use Rx\Scheduler;


/** @noinspection PhpUnhandledExceptionInspection */
Scheduler::setDefaultFactory(static function () {
    return new Scheduler\EventLoopScheduler(Loop::get());
});

$listenUrl = "tcp://127.0.0.1:42252";
$socketAcceptor = CallableSocketAcceptor::handle(static function ($setupPayload, $sendingRSocket) {
    return AbstractRSocket::requestResponseHandler(static function ($payload) {
        print('Received:' . $payload->getDataUtf8() . PHP_EOL);
        return Observable::of(Payload::fromText("metadata", "PONG"));
    });
});
$server = RSocketServer::create($socketAcceptor)->bind($listenUrl);
echo "RSocket Server started on ${listenUrl}\n";
Loop::get()->run();
