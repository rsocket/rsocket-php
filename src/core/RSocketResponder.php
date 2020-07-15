<?php


namespace RSocket\core;


use React\EventLoop\LoopInterface;
use React\Promise\Promise;
use React\Promise\Deferred;
use React\Socket\ConnectionInterface;
use React\Socket\ServerInterface;
use RSocket\ConnectionSetupPayload;
use RSocket\DuplexConnection;
use RSocket\frame\FrameCodec;
use RSocket\frame\FrameType;
use RSocket\io\Closeable;
use RSocket\SocketAcceptor;
use RSocket\transport\TcpDuplexConnection;

class RSocketResponder implements Closeable
{
    private string $url;
    private ServerInterface $serverInterface;
    private SocketAcceptor $socketAcceptor;
    private LoopInterface $loop;

    public function __construct(string $url, ServerInterface $serverInterface, SocketAcceptor $socketAcceptor, LoopInterface $loop)
    {
        $this->url = $url;
        $this->serverInterface = $serverInterface;
        $this->socketAcceptor = $socketAcceptor;
        $this->loop = $loop;
    }


    public function accept(): void
    {
        $this->serverInterface->on('connection', function (ConnectionInterface $connection) {
            $this->receiveConnection(new TcpDuplexConnection($connection))->then();
        });
    }

    private function receiveConnection(DuplexConnection $duplexConn): Promise
    {
        $deferred = new Deferred();
        $socketAcceptor = $this->socketAcceptor;
        $rsocketRequesters = [];
        $duplexConn->receiveHandler = function ($chunk) use (&$duplexConn, &$socketAcceptor, &$deferred, &$rsocketRequesters) {
            $frames = FrameCodec::parseFrames($chunk);
            foreach ($frames as $frame) {
                $header = $frame->header;
                if ($header->type === FrameType::$SETUP) {
                    $setupFrame = $frame;
                    $setupPayload = new ConnectionSetupPayload();
                    $setupPayload->setDataMimeType($setupFrame->dataMimeType);
                    $setupPayload->setMetadataMimeType($setupFrame->metadataMimeType);
                    $setupPayload->setKeepAliveInterval($setupFrame->keepAliveInterval);
                    $setupPayload->setKeepAliveMaxLifetime($setupFrame->keepAliveMaxLifetime);
                    if (!is_null($setupFrame->payload)) {
                        $setupPayload->metadata = $setupFrame->payload->metadata;
                        $setupPayload->data = $setupFrame->payload->data;
                    }
                    $temp = new RSocketRequester($this->loop, $duplexConn, $setupPayload, 'responder');
                    $responder = $socketAcceptor->accept($setupPayload, $temp);
                    if (is_null($responder)) {
                        $message = "Failed to accept RSocket connection";
                        $duplexConn->write(FrameCodec::encodeErrorFrame(0, 0x00000003, $message));
                        $duplexConn->close();
                        $deferred->reject($message);
                        break;
                    }
                    $temp->setResponder($responder);
                    $rsocketRequesters[0] = $temp;
                } else if (!is_null($rsocketRequesters[0])) {
                    $rsocketRequesters[0]->receiveFrame($frame);
                } else {
                    $duplexConn->write(FrameCodec::encodeErrorFrame(0, 0x00000003, "Unsupported"));
                    $duplexConn->close();
                }
            }
        };
        $duplexConn->init();
        return $deferred->promise();
    }

    public function close(): void
    {
        $this->serverInterface->close();
    }
}