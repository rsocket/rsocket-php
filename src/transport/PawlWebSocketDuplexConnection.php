<?php


namespace RSocket\transport;


use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\Message;
use RSocket\DuplexConnection;
use RSocket\io\ByteBuffer;

class PawlWebSocketDuplexConnection extends DuplexConnection
{
    private WebSocket $webSocket;
    private bool $closed = false;

    public function __construct(WebSocket $webSocket)
    {
        $this->webSocket = $webSocket;
    }

    public function close(): void
    {
        if (!$this->closed) {
            $this->closed = true;
            $this->_availability = 0.0;
            $this->webSocket->close();
            if ($this->closeHandler !== null) {
                ($this->closeHandler)();
            }
        }
    }

    public function write(array $frameArray): void
    {
        $wsFrameArray = array_slice($frameArray, 3);
        $this->webSocket->send(pack('C*', ...$wsFrameArray));
    }

    public function init(): void
    {
        $receiveHandler = $this->receiveHandler;
        $this->webSocket->on("message", function (Message $msg) use (&$receiveHandler) {
            // remove 2 bytes from message contents
            $binaryArray = array_slice(unpack('C*', $msg->getContents()), 2);
            // append frame length 3 bytes
            $lenBytes = ByteBuffer::i24ToByteArray(count($binaryArray));
            array_splice($binaryArray, 0, 0, $lenBytes);
            $receiveHandler($binaryArray);
        });
        $this->webSocket->on("close", function () {
            $this->close();
        });
    }
}