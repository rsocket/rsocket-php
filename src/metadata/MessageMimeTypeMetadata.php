<?php


namespace RSocket\metadata;

use RSocket\utils\UTF8;

class MessageMimeTypeMetadata extends MetadataEntry
{
    public string $dataMimeType;

    public function __construct(string $dataMimeType)
    {
        $this->dataMimeType = $dataMimeType;
        $this->mimeType = 'message/x.rsocket.mime-type.v0';
        if (WellKnownMimeType::isWellKnownType($dataMimeType)) {
            $this->content = [];
            $this->content[] = 0x80 | WellKnownMimeType::getMimeTypeId($dataMimeType);
        } else {
            $dataMimeTypeU8Array = UTF8::encode($dataMimeType);
            $content = [];
            $content[] = count($dataMimeTypeU8Array);
            array_push($content, ...$dataMimeTypeU8Array);
            $this->content = $content;
        }
    }

}