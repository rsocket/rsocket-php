<?php


namespace RSocket\routing;

use RSocket\ConnectionSetupPayload;
use RSocket\RSocket;
use RSocket\SocketAcceptor;

class ServiceRouterSocketAcceptor implements SocketAcceptor
{
    private RSocketServiceRouter $router;

    public function __construct(RSocketServiceRouter $router)
    {
        $this->router = $router;
    }

    public function accept(ConnectionSetupPayload $setup, RSocket $sendingSocket): ?RSocket
    {
        return new ServiceRouterRSocket($this->router);
    }
}