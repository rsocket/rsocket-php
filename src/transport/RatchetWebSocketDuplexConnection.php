<?php


namespace RSocket\transport;


use RSocket\DuplexConnection;
use Ratchet\ConnectionInterface;

class RatchetWebSocketDuplexConnection extends DuplexConnection
{
    private ConnectionInterface $wsConn;

    public function __construct(ConnectionInterface $wsConn)
    {
        $this->wsConn = $wsConn;
    }


    public function close(): void
    {
        $this->wsConn->close();
    }

    public function write(array $frameArray): void
    {
        $wsFrameArray = array_slice($frameArray, 3);
        $this->wsConn->send(pack('C*', ...$wsFrameArray));
    }

    public function init(): void
    {

    }
}