<?php

namespace RSocket;

use RSocket\utils\UTF8;

class Payload
{
    public ?array $metadata = null;
    public ?array $data = null;

    public static function fromText(string $metadata, string $data): Payload
    {
        $payload = new self();
        $payload->data = unpack('C*', $data);
        $payload->metadata = unpack('C*', $metadata);
        return $payload;
    }

    public static function fromArray(?array $metadata, ?array $data): Payload
    {
        $payload = new self();
        $payload->data = $data;
        $payload->metadata = $metadata;
        return $payload;
    }

    public function getMetadataUtf8(): string
    {
        return UTF8::decode($this->metadata);
    }

    public function getDataUtf8(): string
    {
        return UTF8::decode($this->data);
    }
}