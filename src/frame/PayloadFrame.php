<?php


namespace RSocket\frame;

use RSocket\io\ByteBuffer;
use RSocket\Payload;

class PayloadFrame extends RSocketFrame
{
    public ?Payload $payload = null;
    public bool $completed = false;

    public static function fromBuffer(RSocketHeader $header, ByteBuffer $buffer): PayloadFrame
    {
        $frame = new self();
        $frame->header = $header;
        $frame->completed = ($header->flags & 0x40) > 0;
        if ($header->frameLength > 0) {
            $frame->payload = self::decodePayload($buffer, $header);
        }
        return $frame;
    }


}