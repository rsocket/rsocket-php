<?php


namespace RSocket\frame;

use RSocket\io\ByteBuffer;
use RSocket\Payload;

class RequestStreamFrame extends RSocketFrame
{
    public ?Payload $payload = null;
    public int $initialRequestN = 0x7FFFFFFF;

    public static function fromBuffer(RSocketHeader $header, ByteBuffer $buffer): RequestStreamFrame
    {
        $frame = new self();
        $frame->header = $header;
        $requestN = $buffer->readI32();
        if ($requestN !== null) {
            $frame->initialRequestN = $requestN;
        }
        if ($header->frameLength > 0) {
            $frame->payload = self::decodePayload($buffer, $header);
        }
        return $frame;
    }
}