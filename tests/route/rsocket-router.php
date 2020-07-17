<?php

require '../../vendor/autoload.php';

use RSocket\AbstractRSocket;
use RSocket\CallableSocketAcceptor;
use RSocket\Payload;
use RSocket\routing\JsonSupport;
use RSocket\routing\RSocketServiceRouter;
use RSocket\routing\ServiceRouterSocketAcceptor;
use RSocket\RSocketServer;
use Rx\Observable;
use Rx\Scheduler;

$loop = React\EventLoop\Factory::create();

/** @noinspection PhpUnhandledExceptionInspection */
Scheduler::setDefaultFactory(function () use ($loop) {
    return new Scheduler\EventLoopScheduler($loop);
});

class Account extends JsonSupport
{
    public string $nick;
    public int $age;
    public string $city;
}

class AccountServiceImpl
{
    public function findAll(): Observable
    {
        return Observable::fromArray(["first", "second"]);
    }

    public function findById(string $id): Observable
    {
        return Observable::of("nick: " . $id);
    }


    public function findUserByNick(string $nick): Observable
    {
        $user = new Account();
        $user->nick = $nick;
        $user->age = 40;
        $user->city = "San Francisco";
        return Observable::of($user);
    }
}

$router = new RSocketServiceRouter();

$router->addService("com.example.AccountService", new AccountServiceImpl());

$listenUri = "tcp://127.0.0.1:42252";
$socketAcceptor = new ServiceRouterSocketAcceptor($router);
$server = RSocketServer::create($loop, $socketAcceptor)->bind($listenUri);
echo "RSocket Server started on ${listenUri}\n";
$loop->run();






