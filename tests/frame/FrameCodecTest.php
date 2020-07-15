<?php


namespace frame;

use PHPUnit\Framework\TestCase;
use RSocket\ConnectionSetupPayload;
use RSocket\frame\FrameCodec;
use RSocket\Payload;

class FrameCodecTest extends TestCase
{

    public function testEncodeSetupFrame(): void
    {
        $setupPayload = new ConnectionSetupPayload();
        $setupPayload->data = [1, 2, 3];
        $setupPayload->metadata = [1, 2, 3];
        $data = FrameCodec::encodeSetupFrame(
            $setupPayload->getKeepAliveInterval(),
            $setupPayload->getKeepAliveMaxLifetime(),
            $setupPayload->getMetadataMimeType(),
            $setupPayload->getDataMimeType(),
            $setupPayload
        );
        var_dump($data);
    }

    public function testEncodeRequestResponseFrame(): void
    {
        $payload = Payload::fromArray([1], [1]);
        $data = FrameCodec::encodeRequestResponseFrame(1, $payload);
        var_dump($data);
    }

    public function testParseFrame(): void
    {
        $chunk = [0x00, 0x00, 0x0D, 0x00, 0x00, 0x00, 0x01, 0x29, 0x60, 0x00, 0x00, 0x00, 0x70, 0x6F, 0x6E, 0x67];
        $frames = FrameCodec::parseFrames($chunk);
        foreach ($frames as $frame) {
            var_dump($frame);
        }
    }
}