<?php


namespace RSocket\metadata;


use RSocket\io\ByteBuffer;
use RSocket\utils\UTF8;

class TaggingMetadata extends MetadataEntry
{
    public array $tags;

    public function __construct(string $mimeType, array $tags)
    {
        $this->mimeType = $mimeType;
        $this->tags = $tags;

        $buffer = new ByteBuffer();
        foreach ($tags as $tag) {
            $tagU8Array = UTF8::encode($tag);
            $tagLength = count($tagU8Array);
            if ($tagLength <= 0xFF) {
                $buffer->writeI8($tagLength);
                $buffer->writeBytes($tagU8Array);
            }
        }
        $this->content = $buffer->toUint8Array();
    }

    public static function fromEntry(MetadataEntry $entry): TaggingMetadata
    {
        $buffer = ByteBuffer::wrap($entry->content);
        $tags = [];
        while ($buffer->isReadable()) {
            $tagLength = $buffer->readI8();
            if ($tagLength !== null) {
                $u8Array = $buffer->readBytes($tagLength);
                if ($u8Array !== null) {
                    $tags[] = UTF8::decode($u8Array);
                }
            }
        }
        return new TaggingMetadata($entry->mimeType, $tags);
    }
}