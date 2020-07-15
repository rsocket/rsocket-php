<?php


namespace RSocket\frame;

use RSocket\io\ByteBuffer;

class RSocketHeader
{
    public int $frameLength = 0;
    public int $streamId = 0;
    public int $type = 0;
    public int $flags = 0;
    public bool $metaPresent = false;

    public function __construct(ByteBuffer $buffer)
    {
        $frameLength = $buffer->readI24();
        if ($frameLength !== null) {
            $this->frameLength = $frameLength;
        }
        $streamId = $buffer->readI32();
        if ($streamId !== null) {
            $this->streamId = $streamId;
        }
        $frameTypeByte = $buffer->readI8();
        if ($frameTypeByte !== null) {
            $this->type = $frameTypeByte >> 2;
            $this->metaPresent = ($frameTypeByte & 0x01) === 1;
        }
        $flags = $buffer->readI8();
        if ($flags !== null) {
            $this->flags = $flags;
        }
    }

    public function __toString(): string
    {
        $text = json_encode($this);
        if (is_string($text)) {
            return $text;
        }
        return "";
    }


}