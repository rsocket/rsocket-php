<?php


namespace metadata;

use PHPUnit\Framework\TestCase;
use RSocket\metadata\WellKnownMimeType;

class WellKnownMimeTypeTest extends TestCase
{

    public function testGetMimeType(): void
    {
        $result = WellKnownMimeType::isWellKnownTypeId(0x00);
        self::assertTrue($result);
        $id = WellKnownMimeType::getMimeTypeId('application/avro');
        self::assertEquals(0, $id);
    }
}