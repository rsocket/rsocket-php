<?php


namespace RSocket\core;


use Exception;

class RSocketException extends Exception
{
    public static int $RESERVED = 0x00000000;
    public static int $INVALID_SETUP = 0x00000001;
    public static int $UNSUPPORTED_SETUP = 0x00000002;
    public static int $REJECTED_SETUP = 0x00000003;
    public static int $REJECTED_RESUME = 0x00000004;
    public static int $CONNECTION_ERROR = 0x00000101;
    public static int $CONNECTION_CLOSE = 0x00000102;
    public static int $APPLICATION_ERROR = 0x00000201;
    public static int $REJECTED = 0x00000202;
    public static int $CANCELED = 0x00000203;
    public static int $INVALID = 0x00000204;

    public static function applicationError(string $message): RSocketException
    {
        return new self($message, self::$APPLICATION_ERROR);
    }

    public static function rejectSetup(string $message): RSocketException
    {
        return new self($message, self::$REJECTED_SETUP);
    }
}