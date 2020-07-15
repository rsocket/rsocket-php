<?php


namespace RSocket;


class CallableSocketAcceptor implements SocketAcceptor
{
    private $handler;

    public function __construct(callable $handler)
    {
        $this->handler = $handler;
    }

    public function accept(ConnectionSetupPayload $setup, RSocket $sendingSocket): ?RSocket
    {
        return ($this->handler)($setup, $sendingSocket);
    }

    public static function handle($callable): SocketAcceptor
    {
        return new self($callable);
    }
}