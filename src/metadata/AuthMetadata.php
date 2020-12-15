<?php


namespace RSocket\metadata;

use RSocket\io\ByteBuffer;
use RSocket\utils\UTF8;

class AuthMetadata extends MetadataEntry
{
    public array $authData;
    public int $authTypeId;

    public function __construct(int $authTypeId, array $authData)
    {
        $this->authData = $authData;
        $this->authTypeId = $authTypeId;
        $this->mimeType = 'message/x.rsocket.authentication.v0';
        $content = [];
        $content[] = (0x80 | $this->authTypeId);
        array_push($content, ...$authData);
        $this->content = $content;
    }

    public static function jwt(string $jwtToken): AuthMetadata
    {
        return new AuthMetadata(0x01, UTF8::encode($jwtToken));
    }

    public static function simple(string $username, string $password): AuthMetadata
    {
        $userNameU8Array = UTF8::encode($username);
        $passwordU8Array = UTF8::encode($password);
        $buffer = new ByteBuffer();
        $buffer->writeI24(count($userNameU8Array));
        $buffer->writeBytes($userNameU8Array);
        $buffer->writeBytes($passwordU8Array);
        return new AuthMetadata(0x00, $buffer->toUint8Array());
    }
}