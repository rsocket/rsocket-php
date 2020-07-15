<?php


namespace RSocket;


interface SocketAcceptor
{
    public function accept(ConnectionSetupPayload $setup, RSocket $sendingSocket): ?RSocket;

}