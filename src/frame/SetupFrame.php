<?php


namespace RSocket\frame;

use RSocket\io\ByteBuffer;
use RSocket\Payload;
use RSocket\utils\UTF8;


class SetupFrame extends RSocketFrame
{
    public string $metadataMimeType = 'message/x.rsocket.composite-metadata.v0';
    public string $dataMimeType = 'application/json';
    public int $keepAliveInterval = 20;
    public int $keepAliveMaxLifetime = 90;
    public ?string $resumeToken;
    public bool $leaseEnable = false;
    public ?Payload $payload;

    /** @noinspection PhpUnusedLocalVariableInspection */
    public static function fromBuffer(RSocketHeader $header, ByteBuffer $buffer): SetupFrame
    {
        $frame = new self();
        $frame->header = $header;
        $resumeEnable = ($header->flags & 0x80) > 0;
        $frame->leaseEnable = ($header->flags & 0x40) > 0;
        $majorVersion = $buffer->readI16();
        $minorVersion = $buffer->readI16();
        $keepAliveInterval = $buffer->readI32();
        if ($keepAliveInterval !== null) {
            $frame->keepAliveInterval = $keepAliveInterval;
        }
        $keepAliveMaxLifetime = $buffer->readI32();
        if ($keepAliveMaxLifetime !== null) {
            $frame->keepAliveMaxLifetime = $keepAliveMaxLifetime;
        }
        //resume token extraction
        if ($resumeEnable) {
            $resumeTokenLength = $buffer->readI16();
            if ($resumeTokenLength !== null) {
                $tokenU8Array = $buffer->readBytes($resumeTokenLength);
                if ($tokenU8Array !== null) {
                    $frame->resumeToken = UTF8::decode($tokenU8Array);
                }
            }
        }
        // metadata & data encoding
        $metadataMimeTypeLength = $buffer->readI8();
        if ($metadataMimeTypeLength !== null) {
            $metadataMimeTypeU8Array = $buffer->readBytes($metadataMimeTypeLength);
            if ($metadataMimeTypeU8Array !== null) {
                $frame->metadataMimeType = UTF8::decode($metadataMimeTypeU8Array);
            }
        }
        $dataMimeTypeLength = $buffer->readI8();
        if ($dataMimeTypeLength !== null) {
            $dataMimeTypeU8Array = $buffer->readBytes($dataMimeTypeLength);
            if ($dataMimeTypeU8Array !== null) {
                $frame->dataMimeType = UTF8::decode($dataMimeTypeU8Array);
            }
        }
        $frame->payload = self::decodePayload($buffer, $header);
        return $frame;
    }
}
