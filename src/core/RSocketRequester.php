<?php


namespace RSocket\core;


use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use RSocket\ConnectionSetupPayload;
use RSocket\DuplexConnection;
use RSocket\frame\FrameCodec;
use RSocket\frame\FrameType;
use RSocket\frame\RSocketFrame;
use RSocket\Payload;
use RSocket\RSocket;
use Rx\Observable;
use Rx\Subject\AsyncSubject;

class RSocketRequester implements RSocket
{
    private DuplexConnection $conn;
    private ConnectionSetupPayload $setupPayload;
    private StreamIdSupplier $streamIdSupplier;
    private array $senders = [];
    private RSocket $responder;
    private string $mode;
    private ?TimerInterface $keepAliveTimer = null;
    private bool $closed = false;
    private float $_availability = 1.0;
    private int $MAX_REQUEST_SIZE = 0x7FFFFFFF;
    private LoopInterface $loop;
    /**
     * @var callable error consumer
     */
    private $errorConsumer;

    public function __construct(LoopInterface $loop, DuplexConnection $conn, ConnectionSetupPayload $setupPayload, string $mode)
    {
        $this->loop = $loop;
        $this->conn = $conn;
        $this->responder = EmptyRSocketResponder::getInstance();
        $this->setupPayload = $setupPayload;
        $this->mode = $mode;
        if ($this->mode === "requester") {
            $this->streamIdSupplier = StreamIdSupplier::clientSupplier();
        } else {
            $this->streamIdSupplier = StreamIdSupplier::serverSupplier();
        }
        $this->conn->receiveHandler ??= function ($data) {
            $this->receiveChunk($data);
        };
        $this->conn->closeHandler = function () {
            $this->close();
        };
        $this->errorConsumer = function ($error) {
            print($error);
        };
    }

    public function setResponder(RSocket $responder): void
    {
        if (!is_null($responder)) {
            $this->responder = $responder;
        }
    }

    /**
     * @param callable $errorConsumer
     */
    public function setErrorConsumer(callable $errorConsumer): void
    {
        if (!is_null($errorConsumer)) {
            $this->errorConsumer = $errorConsumer;
        }
    }

    public function sendSetupPayload(): void
    {
        $this->conn->init();
        $this->conn->write($this->setupPayloadFrame());
        if ($this->mode === 'requester') {
            $this->keepAliveTimer = $this->loop->addPeriodicTimer($this->setupPayload->getKeepAliveInterval() / 1000, function () {
                if (!$this->closed) {
                    $this->conn->write(FrameCodec::encodeKeepAlive(false, 0));
                } else {
                    $this->clearInterval();
                }
            });
        }
    }

    public function clearInterval(): void
    {
        if (!is_null($this->keepAliveTimer)) {
            $this->loop->cancelTimer($this->keepAliveTimer);
        }
    }

    private function receiveChunk(array $chunk): void
    {
        $frames = FrameCodec::parseFrames($chunk);
        foreach ($frames as $frame) {
            $this->receiveFrame($frame);
        }
    }

    public function receiveFrame(RSocketFrame $frame): void
    {
        $header = $frame->header;
        $streamId = $header->streamId;
        switch ($header->type) {
            case FrameType::$SETUP:
                break;
            case FrameType::$KEEPALIVE:
                if ($frame->respond) {
                    $this->conn->write(FrameCodec::encodeKeepAlive(false, $frame->lastReceivedPosition));
                }
                break;
            case FrameType::$PAYLOAD:
                if (array_key_exists($streamId, $this->senders)) {
                    $asyncSubject = $this->senders[$streamId];
                    $asyncSubject->onNext($frame->payload);
                    $asyncSubject->onCompleted();
                }
                break;
            case FrameType::$ERROR:
                if (array_key_exists($streamId, $this->senders)) {
                    $error = new RSocketException($frame->code, $frame->message);
                    if ($streamId === 0) {
                        ($this->errorConsumer)($error);
                    } else {
                        $asyncSubject = $this->senders[$streamId];
                        $asyncSubject->onError($error);
                    }
                }
                break;
            case FrameType::$REQUEST_RESPONSE:
                if ($frame->payload !== null) {
                    $this->responder->requestResponse($frame->payload)->subscribe(function ($payload) use ($streamId) {
                        $this->conn->write(FrameCodec::encodePayloadFrame($streamId, true, $payload));
                    }, function ($error) use ($streamId) {
                        $rsocketException = self::convertToRSocketException($error);
                        $this->conn->write(FrameCodec::encodeErrorFrame($streamId, $rsocketException->getCode(), $rsocketException->getMessage()));
                    });
                } else {
                    $this->conn->write(FrameCodec::encodeErrorFrame($streamId, RSocketException::$INVALID, "Payload is null"));
                }
                break;
            case FrameType::$REQUEST_FNF:
                if ($frame->payload !== null) {
                    $this->responder->fireAndForget($frame->payload)->subscribe();
                }
                break;

            case FrameType::$METADATA_PUSH:
                if ($frame->payload !== null) {
                    $this->responder->metadataPush($frame->payload)->subscribe();
                }
                break;
            case FrameType::$REQUEST_STREAM:
                if ($frame->payload !== null) {
                    $this->responder->requestStream($frame->payload)->subscribe(function ($payload) use ($streamId) {
                        $this->conn->write(FrameCodec::encodePayloadFrame($streamId, false, $payload));
                    }, function ($error) use ($streamId) {
                        $rsocketError = self::convertToRSocketException($error);
                        $this->conn->write(FrameCodec::encodeErrorFrame($streamId, $rsocketError->getCode(), $rsocketError->getMessage()));
                    }, function () use ($streamId) {
                        $this->conn->write(FrameCodec::encodePayloadFrame($streamId, true, null));
                    });
                } else {
                    $this->conn->write(FrameCodec::encodeErrorFrame($streamId, RSocketException::$INVALID, "Payload is null"));
                }
                break;
            default:

        }
    }

    public function fireAndForget(Payload $payload): Observable
    {
        $streamId = $this->streamIdSupplier->nextStreamId($this->senders);
        $this->conn->write(FrameCodec::encodeFireAndForgetFrame($streamId, $payload));
        return Observable::empty();
    }

    public function requestResponse(Payload $payload): Observable
    {
        $streamId = $this->streamIdSupplier->nextStreamId($this->senders);
        $this->conn->write(FrameCodec::encodeRequestResponseFrame($streamId, $payload));
        $asyncSubject = new AsyncSubject();
        $asyncSubject->finally(function () use (&$asyncSubject) {
            unset($asyncSubject);
        });
        $this->senders[$streamId] = $asyncSubject;
        return $asyncSubject;
    }

    public function requestStream(Payload $payload): Observable
    {
        $streamId = $this->streamIdSupplier->nextStreamId($this->senders);
        $this->conn->write(FrameCodec::encodeRequestStreamFrame($streamId, $this->MAX_REQUEST_SIZE, $payload));
        $asyncSubject = new AsyncSubject();
        $asyncSubject->finally(function () use (&$asyncSubject) {
            unset($asyncSubject);
        });
        $this->senders[$streamId] = $asyncSubject;
        return $asyncSubject;
    }

    public function requestChannel(Observable $flux): Observable
    {
        return Observable::error(new \Exception("Unsupported"));
    }

    public function metadataPush(Payload $payload): Observable
    {
        $this->conn->write(FrameCodec::encodeFireAndForgetFrame(0, $payload));
        return Observable::empty();
    }

    public function close(): void
    {
        if (!$this->closed) {
            $this->closed = true;
            $this->_availability = 0.0;
            $this->clearInterval();
            $this->conn->close();
        }
    }

    public function availability(): float
    {
        return $this->_availability;
    }

    private function setupPayloadFrame(): array
    {
        return FrameCodec::encodeSetupFrame(
            $this->setupPayload->getKeepAliveInterval(),
            $this->setupPayload->getKeepAliveMaxLifetime(),
            $this->setupPayload->getMetadataMimeType(),
            $this->setupPayload->getDataMimeType(),
            $this->setupPayload
        );
    }

    public function convertToRSocketException($e): RSocketException
    {
        if ($e === null) {
            return RSocketException::applicationError('Error');
        }
        if ($e instanceof RSocketException) {
            return $e;
        }
        return RSocketException::applicationError((string)$e);
    }
}