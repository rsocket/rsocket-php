<?php

namespace io;

use PHPUnit\Framework\TestCase;
use RSocket\io\ByteBuffer;

class ByteBufferTest extends TestCase
{
    public function testRead(): void
    {
        $array = [1, 2, 1, 1];
        $buffer = ByteBuffer::wrap($array);
        print($buffer->readI8());
        print($buffer->readI8());
        print($buffer->readI16());
    }

    public function testWrite(): void
    {
        $buffer = new ByteBuffer();
        $buffer->writeI8(1);
        $buffer->writeI8(2);
        self::assertCount(2, $buffer->toUint8Array());
    }
}
