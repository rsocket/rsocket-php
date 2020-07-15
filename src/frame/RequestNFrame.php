<?php


namespace RSocket\frame;

use RSocket\io\ByteBuffer;

class RequestNFrame extends RSocketFrame
{
    public int  $initialRequestN = 0x7FFFFFFF;

    public static function fromBuffer(RSocketHeader $header, ByteBuffer $buffer): RequestNFrame
    {
        $frame = new self();
        $frame->header = $header;
        $requestN = $buffer->readI32();
        if ($requestN !== null) {
            $frame->initialRequestN = $requestN;
        }
        return $frame;
    }


}