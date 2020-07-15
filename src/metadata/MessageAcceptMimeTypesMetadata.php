<?php


namespace RSocket\metadata;

use RSocket\io\ByteBuffer;
use RSocket\utils\UTF8;

class MessageAcceptMimeTypesMetadata extends MetadataEntry
{
    public array $acceptMimeTypes;

    public function __construct(array $acceptMimeTypes)
    {
        $this->acceptMimeTypes = $acceptMimeTypes;
        $this->mimeType = "message/x.rsocket.accept-mime-types.v0";
        $buffer = new ByteBuffer();
        foreach ($acceptMimeTypes as $acceptMimeType) {
            if (WellKnownMimeType::isWellKnownType($acceptMimeType)) {
                $buffer->writeI8(0x80 | WellKnownMimeType::getMimeTypeId($acceptMimeType));
            } else {
                $acceptMimeTypeU8Array = UTF8::encode($acceptMimeType);
                $buffer->writeI8(count($acceptMimeTypeU8Array));
                $buffer->writeBytes($acceptMimeTypeU8Array);
            }
        }
        $this->content = $buffer->toUint8Array();
    }

}