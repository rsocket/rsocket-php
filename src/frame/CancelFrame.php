<?php


namespace RSocket\frame;


use RSocket\io\ByteBuffer;

class CancelFrame extends RSocketFrame
{

    public static function fromBuffer(RSocketHeader $header, ByteBuffer $buffer): CancelFrame
    {
        $frame = new self();
        $frame->header = $header;
        return $frame;
    }
}