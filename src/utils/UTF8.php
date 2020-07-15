<?php


namespace RSocket\utils;


class UTF8
{
    public static function encode(?string $text): array
    {
        return array_values(unpack('C*', $text));
    }

    public static function decode(?array $data): string
    {
        return pack('C*', ...$data);
    }
}