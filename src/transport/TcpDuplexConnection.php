<?php


namespace RSocket\transport;


use React\Socket\ConnectionInterface;
use RSocket\DuplexConnection;

class TcpDuplexConnection extends DuplexConnection
{
    private ConnectionInterface $conn;
    private bool $closed = false;

    /**
     * TcpDuplexConnection constructor.
     * @param ConnectionInterface $conn
     */
    public function __construct(ConnectionInterface $conn)
    {
        $this->conn = $conn;
    }

    public function availability(): float
    {
        return 1.0;
    }

    public function close(): void
    {
        if (!$this->closed) {
            $this->closed = true;
            $this->conn->close();
            if ($this->closeHandler !== null) {
                $handler = $this->closeHandler;
                $handler();
            }
        }
    }

    public function write(array $frameArray): void
    {
        $this->conn->write(pack('C*', ...$frameArray));
    }

    public function init(): void
    {
        $receiveHandler = $this->receiveHandler;
        $this->conn->on("data", function ($data) use (&$receiveHandler) {
            $receiveHandler(array_values(unpack('C*', $data)));
        });
        $this->conn->on("end", function () {
            $this->close();
        });
    }
}