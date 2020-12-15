<?php

require '../../vendor/autoload.php';

use RSocket\routing\RSocketServiceStub;
use RSocket\RSocketConnector;
use Rx\Observable;
use Rx\Scheduler;

interface AccountService
{
    public function findById(string $id): Observable;
}

class RSocketAccountServiceStubs
{
    private Observable $rsocket;

    public function __construct(Observable $rsocket)
    {
        $this->rsocket = $rsocket;
    }

    /**
     * @return \AccountService user service
     */
    public function getAccountService(): object
    {
        return new RSocketServiceStub("com.example.AccountService", $this->rsocket);
    }

}

$loop = React\EventLoop\Factory::create();

/** @noinspection PhpUnhandledExceptionInspection */
Scheduler::setDefaultFactory(function () use ($loop) {
    return new Scheduler\EventLoopScheduler($loop);
});

$rsocket = Observable::fromPromise(RSocketConnector::create($loop)->connect("tcp://127.0.0.1:42252"));

$stubs = new RSocketAccountServiceStubs($rsocket);

$accountService = $stubs->getAccountService();

$accountService->findById("1")->subscribe(
    function (string $name) {
        echo 'Result: ' . $name . PHP_EOL;
    }
);

$loop->run();
