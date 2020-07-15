<?php


namespace RSocket\frame;


use RSocket\io\ByteBuffer;
use RSocket\Payload;

class KeepAliveFrame extends RSocketFrame
{
    public int $lastReceivedPosition = 0;
    public ?Payload $payload;
    public bool $respond = false;

    public static function fromBuffer(RSocketHeader $header, ByteBuffer $buffer): KeepAliveFrame
    {
        $frame = new self();
        $frame->header = $header;
        $lastReceivedPosition = $buffer->readI32();
        if ($lastReceivedPosition !== null) {
            $frame->lastReceivedPosition = $lastReceivedPosition;
        }
        if ($header->frameLength > 0) {
            $frame->payload = self::decodePayload($buffer, $header);
        }
        $frame->respond = ($header->flags & 0x80) > 0;
        return $frame;
    }
}