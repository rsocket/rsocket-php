<?php

require '../../vendor/autoload.php';

use React\EventLoop\Loop;
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


/** @noinspection PhpUnhandledExceptionInspection */
Scheduler::setDefaultFactory(function () {
    return new Scheduler\EventLoopScheduler(Loop::get());
});

$rsocket = Observable::fromPromise(RSocketConnector::create()->connect("tcp://127.0.0.1:42252"));

$stubs = new RSocketAccountServiceStubs($rsocket);

$accountService = $stubs->getAccountService();

$accountService->findById("1")->subscribe(
    function (string $name) {
        echo 'Result: ' . $name . PHP_EOL;
    }
);

Loop::get()->run();
