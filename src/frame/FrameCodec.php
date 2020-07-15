<?php


namespace RSocket\frame;

use RSocket\io\ByteBuffer;
use RSocket\Payload;
use RSocket\utils\UTF8;

class FrameCodec
{
    public static int $MAJOR_VERSION = 1;
    public static int $MINOR_VERSION = 0;

    /**
     * @param array $chunk
     * @return iterable<RSocketFrame>
     */
    public static function parseFrames(array $chunk)
    {
        $buffer = ByteBuffer::wrap($chunk);
        while ($buffer->isReadable()) {
            $frame = self::parseFrame($buffer);
            if ($frame !== null) {
                yield $frame;
            }
        }
    }

    public static function parseFrame(ByteBuffer $buffer): ?RSocketFrame
    {
        $header = new RSocketHeader($buffer);
        $frame = null;
        switch ($header->type) {
            case FrameType::$SETUP:
                $frame = SetupFrame::fromBuffer($header, $buffer);
                break;
            case FrameType::$KEEPALIVE:
                $frame = KeepAliveFrame::fromBuffer($header, $buffer);
                break;
            case FrameType::$PAYLOAD:
                $frame = PayloadFrame::fromBuffer($header, $buffer);
                break;
            case FrameType::$REQUEST_RESPONSE:
                $frame = RequestResponseFrame::fromBuffer($header, $buffer);
                break;
            case FrameType::$REQUEST_FNF:
                $frame = RequestFNFFrame::fromBuffer($header, $buffer);
                break;
            case FrameType::$REQUEST_STREAM:
                $frame = RequestStreamFrame::fromBuffer($header, $buffer);
                break;
            case FrameType::$REQUEST_CHANNEL:
                $frame = RequestChannelFrame::fromBuffer($header, $buffer);
                break;
            case FrameType::$METADATA_PUSH:
                $frame = MetadataPushFrame::fromBuffer($header, $buffer);
                break;
            case FrameType::$ERROR:
                $frame = ErrorFrame::fromBuffer($header, $buffer);
                break;
            case FrameType::$CANCEL:
                $frame = CancelFrame::fromBuffer($header, $buffer);
                break;
            case FrameType::$REQUEST_N:
                $frame = RequestNFrame::fromBuffer($header, $buffer);
                break;
            case FrameType::$LEASE:
                $frame = LeaseFrame::fromBuffer($header, $buffer);
                break;
            default:
                if ($header->frameLength > 9) {
                    $buffer->readBytes($header->frameLength - 9);
                }
        }
        return $frame;
    }

    public static function encodeSetupFrame(int $keepaliveInterval, int $maxLifetime,
                                            string $metadataMimeType, string $dataMimeType, ?Payload $payload = null): array
    {
        $frameBuffer = new ByteBuffer();
        $frameBuffer->writeI24(0); // frame length
        $frameBuffer->writeI32(0); //stream id
        //frame type with metadata indicator without resume token and lease
        $metadata = null;
        if ($payload !== null) {
            $metadata = $payload->metadata;
        }
        self::writeTFrameTypeAndFlags($frameBuffer, FrameType::$SETUP, $metadata, 0);
        $frameBuffer->writeI16(self::$MAJOR_VERSION);
        $frameBuffer->writeI16(self::$MINOR_VERSION);
        $frameBuffer->writeI32($keepaliveInterval);
        $frameBuffer->writeI32($maxLifetime);
        //Metadata Encoding MIME Type
        $metadataMimeTypeBytes = UTF8::encode($metadataMimeType);
        $frameBuffer->writeI8(count($metadataMimeTypeBytes));
        $frameBuffer->writeBytes($metadataMimeTypeBytes);
        //Data Encoding MIME Type
        $dataMimeTypeBytes = UTF8::encode($dataMimeType);
        $frameBuffer->writeI8(count($dataMimeTypeBytes));
        $frameBuffer->writeBytes($dataMimeTypeBytes);
        // Metadata & Setup Payload
        self::writePayload($frameBuffer, $payload);
        // refill frame length
        self::refillFrameLength($frameBuffer);
        return $frameBuffer->toUint8Array();
    }

    public static function encodeKeepAlive(bool $respond, int $lastPosition): array
    {
        $frameBuffer = new ByteBuffer();
        $frameBuffer->writeI24(0); // frame length
        $frameBuffer->writeI32(0); //stream id
        $frameBuffer->writeI8(FrameType::$KEEPALIVE << 2);
        if ($respond) {
            $frameBuffer->writeI8(0x80);
        } else {
            $frameBuffer->writeI8(0);
        }
        $frameBuffer->writeI64($lastPosition);
        self::refillFrameLength($frameBuffer);
        return $frameBuffer->toUint8Array();
    }

    public static function encodeRequestResponseFrame(int $streamId, Payload $payload): array
    {
        $frameBuffer = new ByteBuffer();
        $frameBuffer->writeI24(0); // frame length
        $frameBuffer->writeI32($streamId); //stream id
        self::writeTFrameTypeAndFlags($frameBuffer, FrameType::$REQUEST_RESPONSE, $payload->metadata, 0);
        self::writePayload($frameBuffer, $payload);
        self::refillFrameLength($frameBuffer);
        return $frameBuffer->toUint8Array();
    }

    public static function encodeRequestStreamFrame(int $streamId, int $initialRequestN, Payload $payload): array
    {
        $frameBuffer = new ByteBuffer();
        $frameBuffer->writeI24(0); // frame length
        $frameBuffer->writeI32($streamId); //stream id
        self::writeTFrameTypeAndFlags($frameBuffer, FrameType::$REQUEST_STREAM, $payload->metadata, 0);
        $frameBuffer->writeI32($initialRequestN);
        self::writePayload($frameBuffer, $payload);
        self::refillFrameLength($frameBuffer);
        return $frameBuffer->toUint8Array();
    }

    public static function encodeFireAndForgetFrame(int $streamId, Payload $payload): array
    {
        $frameBuffer = new ByteBuffer();
        $frameBuffer->writeI24(0); // frame length
        $frameBuffer->writeI32($streamId); //stream id
        self::writeTFrameTypeAndFlags($frameBuffer, FrameType::$REQUEST_FNF, $payload->metadata, 0);
        self::writePayload($frameBuffer, $payload);
        self::refillFrameLength($frameBuffer);
        return $frameBuffer->toUint8Array();
    }

    public static function encodeMetadataPushFrame(int $streamId, Payload $payload): array
    {
        $frameBuffer = new ByteBuffer();
        $frameBuffer->writeI24(0); // frame length
        $frameBuffer->writeI32($streamId); //stream id
        $frameBuffer->writeI8((FrameType::$METADATA_PUSH << 2) | 0x01);
        $frameBuffer->writeI8(0);
        $frameBuffer->writeBytes($payload->metadata);
        self::refillFrameLength($frameBuffer);
        return $frameBuffer->toUint8Array();
    }

    public static function encodePayloadFrame(int $streamId, bool $completed, Payload $payload): array
    {
        $frameBuffer = new ByteBuffer();
        $frameBuffer->writeI24(0); // frame length
        $frameBuffer->writeI32($streamId); //stream id
        $flags = 0;
        if ($completed) {
            $flags |= 0x40; //complete
        } else {
            $flags |= 0x20; //next
        }
        self::writeTFrameTypeAndFlags($frameBuffer, FrameType::$PAYLOAD, $payload->metadata, $flags);
        self::writePayload($frameBuffer, $payload);
        self::refillFrameLength($frameBuffer);
        return $frameBuffer->toUint8Array();
    }

    public static function encodeErrorFrame(int $streamId, int $code, string $message): array
    {
        $frameBuffer = new ByteBuffer();
        $frameBuffer->writeI24(0); // frame length
        $frameBuffer->writeI32($streamId); //stream id
        $frameBuffer->writeI8(FrameType::$ERROR << 2);
        $frameBuffer->writeI8(0);
        $frameBuffer->writeI32($code);
        $frameBuffer->writeBytes(UTF8::encode($message));
        self::refillFrameLength($frameBuffer);
        return $frameBuffer->toUint8Array();
    }

    private static function writeTFrameTypeAndFlags(ByteBuffer $frameBuffer, int $frameType, ?array $metadata, int $flags): void
    {
        if ($metadata !== null) {
            $frameBuffer->writeI8($frameType << 2 | 1);
        } else {
            $frameBuffer->writeI8($frameType << 2);
        }
        $frameBuffer->writeI8($flags);
    }

    private static function writePayload(ByteBuffer $frameBuffer, ?Payload $payload): void
    {
        if ($payload !== null) {
            $metadata = $payload->metadata;
            if ($metadata !== null) {
                $frameBuffer->writeI24(count($metadata));
                $frameBuffer->writeBytes($metadata);
            }
            $data = $payload->data;
            if ($data !== null) {
                $frameBuffer->writeBytes($data);
            }
        }
    }

    private static function refillFrameLength(ByteBuffer $frameBuffer): void
    {
        $frameLength = count($frameBuffer->toUint8Array()) - 3;
        $frameBuffer->resetWriterIndex();
        $frameBuffer->writeI24($frameLength);
    }

}