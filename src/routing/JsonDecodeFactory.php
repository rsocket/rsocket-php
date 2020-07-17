<?php


namespace RSocket\routing;


class JsonDecodeFactory
{
    private static array $decodeHandlers = [];

    public static function addHandler(string $methodFullName, callable $decodeHandler): void
    {
        self::$decodeHandlers[$methodFullName] = $decodeHandler;
    }

    public static function getHandler(string $methodFullName): ?callable
    {
        if (array_key_exists($methodFullName, self::$decodeHandlers)) {
            return self::$decodeHandlers[$methodFullName];
        }
        return null;
    }

}