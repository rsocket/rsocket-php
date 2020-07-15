<?php


namespace RSocket\metadata;

use RSocket\io\ByteBuffer;
use RSocket\utils\UTF8;

class CompositeMetadata
{
    private ByteBuffer $buffer;

    public function __construct(ByteBuffer $buffer)
    {
        $this->buffer = $buffer;
    }

    public static function create(): self
    {
        return new CompositeMetadata(new ByteBuffer());
    }

    public static function fromU8Array(array $u8Array): self
    {
        return new CompositeMetadata(ByteBuffer::wrap($u8Array));
    }

    public static function fromEntries(array $entries): self
    {
        $compositeMetadata = new CompositeMetadata(new ByteBuffer());
        foreach ($entries as $entry) {
            $compositeMetadata->addMetadata($entry);
        }
        return $compositeMetadata;
    }

    public function addMetadata(MetadataEntry $metadata): void
    {
        if (WellKnownMimeType::isWellKnownType($metadata->mimeType)) {
            $this->addWellKnownMimeType(WellKnownMimeType::getMimeTypeId($metadata->mimeType), $metadata->content);
        } else {
            $this->addExplicitMimeType($metadata->mimeType, $metadata->content);
        }
    }

    public function addWellKnownMimeType(int $typeId, array $content): void
    {
        $this->buffer->writeI8($typeId | 0x80);
        $this->buffer->writeI24(count($content));
        $this->buffer->writeBytes($content);
    }

    public function addExplicitMimeType(string $mimeType, array $content): void
    {
        if (WellKnownMimeType::isWellKnownType($mimeType)) {
            $this->addWellKnownMimeType(WellKnownMimeType::getMimeTypeId($mimeType), $content);
        } else {
            $mimeTypeArray = UTF8::encode($mimeType);
            $this->buffer->writeI8(count($mimeTypeArray));
            $this->buffer->writeBytes($mimeTypeArray);
            $this->buffer->writeI24(count($content));
            $this->buffer->writeBytes($content);
        }
    }

    public function getAllEntries(): iterable
    {
        $entries = [];
        $buffer = $this->buffer;
        while ($buffer->isReadable()) {
            $metadataTypeOrLength = $buffer->readI8();
            if ($metadataTypeOrLength !== null) {
                if (($metadataTypeOrLength >= 0x80)) {
                    $typeId = $metadataTypeOrLength - 0x80;
                    $wellKnownMimeType = WellKnownMimeType::getMimeType($typeId);
                    $dataLength = $buffer->readI24();
                    if ($dataLength !== null) {
                        $content = $buffer->readBytes($dataLength);
                        if ($content !== null) {
                            $entries[] = MetadataEntry::wellKnown($typeId, $wellKnownMimeType, $content);
                        }
                    }
                } else {
                    $mimeTypeU8Array = $buffer->readBytes($metadataTypeOrLength);
                    if ($mimeTypeU8Array !== null) {
                        $dataLength = $buffer->readI24();
                        if ($dataLength !== null) {
                            $content = $buffer->readBytes($dataLength);
                            if ($content !== null) {
                                $mimeType = UTF8::decode($mimeTypeU8Array);
                                $entries[] = MetadataEntry::explicit($mimeType, $content);
                            }
                        }
                    }
                }
            }
        }
        return $entries;
    }

    public function toUint8Array(): array
    {
        return $this->buffer->toUint8Array();
    }
}