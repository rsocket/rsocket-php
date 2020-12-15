<?php


namespace RSocket\core;


use Exception;
use React\EventLoop\LoopInterface;
use React\Socket\Server;
use RSocket\frame\FrameCodec;
use RSocket\frame\FrameType;
use RSocket\io\ByteBuffer;
use RSocket\io\Closeable;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\WsServer;
use RSocket\SocketAcceptor;
use RSocket\transport\RatchetWebSocketDuplexConnection;


class RatchetWebSocketRSocketResponder extends RSocketBaseResponder implements Closeable, MessageComponentInterface
{
    private string $url;
    private SocketAcceptor $socketAcceptor;
    private LoopInterface $loop;
    private Server $socketServer;
    private array $handlers = [];

    public function __construct(string $url, SocketAcceptor $socketAcceptor, LoopInterface $loop)
    {
        $this->url = $url;
        $urlArray = parse_url($url);
        $this->socketAcceptor = $socketAcceptor;
        $this->loop = $loop;
        $wsServer = new WsServer($this);
        $this->socketServer = new Server($urlArray['host'] . ':' . $urlArray['port'], $loop);
        // start http server with websocket support
        new IoServer(new HttpServer($wsServer), $this->socketServer, $this->loop);
    }

    public function onOpen(ConnectionInterface $conn): void
    {

    }

    public function onMessage(ConnectionInterface $from, $msg): void
    {
        $conId = spl_object_hash($from);
        // remove 2 bytes from message contents
        $chunk = unpack('C*', $msg);
        // append frame length 3 bytes
        $lenBytes = ByteBuffer::i24ToByteArray(count($chunk));
        array_splice($chunk, 0, 0, $lenBytes);

        $frames = FrameCodec::parseFrames($chunk);
        foreach ($frames as $frame) {
            $header = $frame->header;
            if ($header->type === FrameType::$SETUP) {
                $setupPayload = $this->parseSetupPayload($frame);
                $duplexConn = new RatchetWebSocketDuplexConnection($from);
                $temp = new RSocketRequester($this->loop, $duplexConn, $setupPayload, 'responder');
                $responder = $this->socketAcceptor->accept($setupPayload, $temp);
                if (is_null($responder)) {
                    $message = "Failed to accept RSocket connection";
                    $duplexConn->write(FrameCodec::encodeErrorFrame(0, 0x00000003, $message));
                    $duplexConn->close();
                    break;
                }
                $temp->setResponder($responder);
                $this->handlers[$conId] = $temp;
            } else if (array_key_exists($conId, $this->handlers)) {
                $this->handlers[$conId]->receiveFrame($frame);
            } else {
                $from->close();
            }
        }

        /* $duplexConnection = new RatchetWebSocketDuplexConnection($conn);
         new RSocketRequester($this->loop, $duplexConnection)*/
    }

    public function onClose(ConnectionInterface $conn): void
    {
        $conId = spl_object_hash($conn);
        if (!array_key_exists($conId, $this->handlers)) {
            $this->handlers[$conId]->close();
        }

    }

    public function onError(ConnectionInterface $conn, Exception $e): void
    {
    }

    public function close(): void
    {
        $this->socketServer->close();
    }
}