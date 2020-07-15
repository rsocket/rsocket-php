<?php


namespace RSocket\frame;


use RSocket\io\ByteBuffer;
use RSocket\Payload;

class RequestChannelFrame extends RSocketFrame
{
    public ?Payload $payload = null;
    public ?int $initialRequestN = null;

    public static function fromBuffer(RSocketHeader $header, ByteBuffer $buffer): RequestChannelFrame
    {
        $frame = new self();
        $frame->header = $header;
        $frame->initialRequestN = $buffer->readI32();
        if ($header->frameLength > 0) {
            $frame->payload = self::decodePayload($buffer, $header);
        }
        return $frame;
    }
}