<?php


namespace RSocket\frame;


use RSocket\io\ByteBuffer;
use RSocket\Payload;

class MetadataPushFrame extends RSocketFrame
{
    public ?Payload $payload = null;

    public static function fromBuffer(RSocketHeader $header, ByteBuffer $buffer): MetadataPushFrame
    {
        $frame = new self();
        $frame->header = $header;
        if ($header->frameLength > 0) {
            $metadataBytes = $buffer->readBytes($header->frameLength - 6);
            $frame->payload = Payload::fromArray($metadataBytes, null);
        }
        return $frame;
    }
}