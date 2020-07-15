<?php


namespace RSocket\frame;

use RSocket\io\ByteBuffer;
use RSocket\Payload;

class RequestResponseFrame extends RSocketFrame
{
    public ?Payload $payload = null;

    public static function fromBuffer(RSocketHeader $header, ByteBuffer $buffer): RequestResponseFrame
    {
        $frame = new self();
        $frame->header = $header;
        if ($header->frameLength > 0) {
            $frame->payload = self::decodePayload($buffer, $header);
        }
        return $frame;
    }
}