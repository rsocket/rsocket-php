<?php


namespace RSocket\frame;

use RSocket\io\ByteBuffer;

class LeaseFrame extends RSocketFrame
{
    public int $timeToLive = 0;
    public int $numberOfRequests = 0;

    public static function fromBuffer(RSocketHeader $header, ByteBuffer $buffer): LeaseFrame
    {
        $frame = new self();
        $frame->header = $header;
        $timeToLive = $buffer->readI32();
        if ($timeToLive !== null) {
            $frame->timeToLive = $timeToLive;
        }
        $numberOfRequests = $buffer->readI32();
        if ($numberOfRequests !== null) {
            $frame->numberOfRequests = $numberOfRequests;
        }
        return $frame;
    }

}