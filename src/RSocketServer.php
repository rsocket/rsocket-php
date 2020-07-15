<?php


namespace RSocket;


use React\EventLoop\LoopInterface;
use  React\Socket\TcpServer;
use RSocket\core\RSocketResponder;
use RSocket\io\Closeable;

class RSocketServer
{
    private SocketAcceptor $socketAcceptor;
    private LoopInterface $loop;

    public static function create(LoopInterface $loop, SocketAcceptor $socketAcceptor): RSocketServer
    {
        $server = new self();
        $server->loop= $loop;
        $server->socketAcceptor = $socketAcceptor;
        return $server;
    }

    public function bind(string $url): Closeable
    {
        $server = new TcpServer($url, $this->loop);
        $responder = new RSocketResponder($url, $server, $this->socketAcceptor, $this->loop);
        $responder->accept();
        return $responder;
    }
}