<?php


namespace RSocket\frame;

use RSocket\io\ByteBuffer;
use RSocket\Payload;

class RSocketFrame
{
    public RSocketHeader $header;

    protected static function decodePayload(ByteBuffer $buffer, RSocketHeader $header): Payload
    {
        $payload = new Payload();
        $dataLength = $header->frameLength - 6;
        if ($header->metaPresent) {
            $metadataLength = $buffer->readI24();
            if ($metadataLength !== null) {
                $dataLength = $dataLength - 3 - $metadataLength;
                if ($metadataLength > 0) {
                    $payload->metadata = $buffer->readBytes($metadataLength);
                }
            }
        }
        if ($dataLength > 0) {
            $payload->data = $buffer->readBytes($dataLength);
        }
        return $payload;
    }
}