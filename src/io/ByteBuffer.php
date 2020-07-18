<?php declare(strict_types=1);


namespace RSocket\io;


class ByteBuffer
{
    public array $buffer;
    private int $readerIndex = 0;
    private int $writerIndex = 0;
    private int $capacity = 0;

    public static function wrap(array $bytes): ByteBuffer
    {
        $bb = new ByteBuffer();
        $bb->buffer = $bytes;
        $bb->capacity = count($bytes);
        return $bb;
    }

    public function __construct()
    {
        $this->buffer = array();
    }


    public function isReadable(): bool
    {
        return $this->readerIndex < $this->capacity;
    }


    public function isWritable(): bool
    {
        return $this->writerIndex < $this->capacity;
    }

    public function rewind(): void
    {
        $this->readerIndex = 0;
        $this->writerIndex = 0;
    }

    public function resetWriterIndex(): void
    {
        $this->writerIndex = 0;
    }

    public function resetReaderIndex(): void
    {
        $this->writerIndex = 0;
    }

    public function capacity(): int
    {
        return $this->capacity;
    }

    public function toUint8Array(): array
    {
        return $this->buffer;
    }

    public function readBytes(int $len): ?array
    {
        if ($this->readerIndex + $len <= $this->capacity) {
            $bytes = array_slice($this->buffer, $this->readerIndex, $len);
            $this->readerIndex += $len;
            return $bytes;
        }
        return null;
    }

    public function readI8(): ?int
    {
        if ($this->readerIndex < $this->capacity) {
            $value = $this->buffer[$this->readerIndex];
            ++$this->readerIndex;
            return $value;
        }
        return null;
    }

    public function readI16(): ?int
    {
        return self::bytesToInt($this->readBytes(2));
    }

    public function readI24(): ?int
    {
        return self::bytesToInt($this->readBytes(3));
    }

    public function readI32(): ?int
    {
        return self::bytesToInt($this->readBytes(4));
    }

    public function readI64(): ?int
    {
        return self::bytesToInt($this->readBytes(8));
    }

    public function writeBytes(array $bytes): void
    {
        if ($this->writerIndex === $this->capacity) {
            array_push($this->buffer, ...$bytes);
        } else {
            array_splice($this->buffer, $this->writerIndex, count($bytes), $bytes);
        }
        $this->writerIndex += count($bytes);
        $this->autoGrow();
    }

    public function insertBytes(array $bytes): void
    {
        array_splice($this->buffer, $this->writerIndex, 0, $bytes);
        $this->writerIndex += count($bytes);
        $this->autoGrow();
    }

    public function writeI8(int $value): void
    {
        if ($this->writerIndex === $this->capacity) {
            $this->buffer[] = $value;
            $this->capacity = count($this->buffer);
        } else {
            $this->buffer[$this->writerIndex] = $value;
        }
        ++$this->writerIndex;
    }

    public function writeI16(int $value): void
    {
        $this->writeBytes(self::i16ToByteArray($value));
    }

    public function writeI24(int $value): void
    {
        $this->writeBytes(self::i24ToByteArray($value));
    }

    public function insertI24(int $value): void
    {
        $this->insertBytes(self::i24ToByteArray($value));
    }

    public function writeI32(int $value): void
    {
        $this->writeBytes(self::i32ToByteArray($value));
    }

    public function writeI64(int $value): void
    {
        $this->writeBytes(self::i64ToByteArray($value));
    }

    public function autoGrow(): void
    {
        $bufferLen = count($this->buffer);
        if ($this->capacity < $bufferLen) {
            $this->capacity = count($this->buffer);
        }
    }

    public static function i64ToByteArray(int $int): array
    {
        $bytes = array(0, 0, 0, 0, 0, 0, 0, 0);
        $bytes[0] = $int >> 56 & 0xFF;
        $bytes[1] = $int >> 48 & 0xFF;
        $bytes[2] = $int >> 40 & 0xFF;
        $bytes[3] = $int >> 32 & 0xFF;
        $bytes[4] = $int >> 24 & 0xFF;
        $bytes[5] = $int >> 16 & 0xFF;
        $bytes[6] = $int >> 8 & 0xFF;
        $bytes[7] = $int & 0xFF;
        return $bytes;
    }

    public static function i32ToByteArray(int $int): array
    {
        $bytes = array(0, 0, 0, 0);
        $bytes[0] = $int >> 24 & 0xFF;
        $bytes[1] = $int >> 16 & 0xFF;
        $bytes[2] = $int >> 8 & 0xFF;
        $bytes[3] = $int & 0xFF;
        return $bytes;
    }

    public static function i24ToByteArray(int $int): array
    {
        $bytes = array(0, 0, 0);
        $bytes[0] = $int >> 16 & 0xFF;
        $bytes[1] = $int >> 8 & 0xFF;
        $bytes[2] = $int & 0xFF;
        return $bytes;
    }

    public static function i16ToByteArray(int $int): array
    {
        $bytes = array(0, 0);
        $bytes[0] = $int >> 8 & 0xFF;
        $bytes[1] = $int & 0xFF;
        return $bytes;
    }

    public static function bytesToInt(?array $bytes): ?int
    {
        if (is_null($bytes)) {
            return null;
        }
        $value = 0;
        foreach ($bytes as $element) {
            $value = ($value * 256) + $element;
        }
        return $value;
    }

}

