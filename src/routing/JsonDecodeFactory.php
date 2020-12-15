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
        return self::$decodeHandlers[$methodFullName] ?? null;
    }

    public static function decodeUtf8Text(?string $utf8text, string $methodFullName): object
    {
        if ($utf8text !== null && $utf8text !== '') {
            $firstChar = $utf8text[0];
            // json text validate & decode
            if ($firstChar === '{' || $firstChar === '[' || $firstChar === '"') {
                $arrayObj = json_decode($utf8text);
                $decodeHandler = self::getHandler($methodFullName);
                if ($decodeHandler !== null) {
                    $decodeHandler($arrayObj);
                }
                return $arrayObj;
            }
        }
        return $utf8text;
    }

}