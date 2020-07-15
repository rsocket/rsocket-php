<?php


namespace RSocket\frame;

use RSocket\io\ByteBuffer;
use RSocket\Payload;
use RSocket\utils\UTF8;

class ErrorFrame extends RSocketFrame
{
    public ?Payload $payload = null;
    public int $code = 0;
    public string $message = '';

    public static function fromBuffer(RSocketHeader $header, ByteBuffer $buffer): ErrorFrame
    {
        $frame = new self();
        $frame->header = $header;
        $code = $buffer->readI32();
        if ($code !== null) {
            $frame->code = $code;
        }
        $dataLength = $header->frameLength - 10;
        if ($dataLength > 0) {
            $u8Array = $buffer->readBytes($dataLength);
            if ($u8Array !== null) {
                $frame->message = UTF8::decode($u8Array);
            }
        }
        return $frame;
    }


}