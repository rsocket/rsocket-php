<?php


namespace RSocket\frame;


class FrameType
{
    public static int $RESERVED = 0x00;
    public static int $SETUP = 0x01;
    public static int $LEASE = 0x02;
    public static int $KEEPALIVE = 0x03;
    public static int $REQUEST_RESPONSE = 0x04;
    public static int $REQUEST_FNF = 0x05;
    public static int $REQUEST_STREAM = 0x06;
    public static int $REQUEST_CHANNEL = 0x07;
    public static int $REQUEST_N = 0x08;
    public static int $CANCEL = 0x09;
    public static int $PAYLOAD = 0x0A;
    public static int $ERROR = 0x0B;
    public static int $METADATA_PUSH = 0x0C;
    public static int $RESUME = 0x0D;
    public static int $RESUME_OK = 0x0E;
    public static int $EXT = 0x3F;
}