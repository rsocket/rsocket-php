<?php


namespace RSocket;


use React\EventLoop\LoopInterface;
use  React\Socket\TcpServer;
use RSocket\core\RatchetWebSocketRSocketResponder;
use RSocket\core\RSocketException;
use RSocket\core\TcpSocketRSocketResponder;
use RSocket\io\Closeable;

class RSocketServer
{
    private SocketAcceptor $socketAcceptor;
    private LoopInterface $loop;

    public static function create(LoopInterface $loop, SocketAcceptor $socketAcceptor): RSocketServer
    {
        $server = new self();
        $server->loop = $loop;
        $server->socketAcceptor = $socketAcceptor;
        return $server;
    }

    public function bind(string $url): Closeable
    {
        $urlArray = parse_url($url);
        if ($urlArray !== false && array_key_exists("scheme", $urlArray)) {
            $scheme = $urlArray['scheme'];
            if ($scheme === 'tcp') {
                $server = new TcpServer($url, $this->loop);
                $responder = new TcpSocketRSocketResponder($url, $server, $this->socketAcceptor, $this->loop);
                $responder->accept();
                return $responder;
            }
            if ($scheme === 'ws') {
                $responder = new RatchetWebSocketRSocketResponder($url, $this->socketAcceptor, $this->loop);
                return $responder;
            }
        }
        throw new RSocketException("Failed to listen " . $url);
    }
}