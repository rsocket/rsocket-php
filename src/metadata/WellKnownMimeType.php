<?php


namespace RSocket\metadata;


class WellKnownMimeType
{
    private static array $MIME_TYPES = [];

    public static function isWellKnownTypeId(int $id): bool
    {
        return array_key_exists($id, self::$MIME_TYPES);
    }

    public static function isWellKnownType(string $mimeType): bool
    {
        return array_key_exists($mimeType, self::$MIME_TYPES);
    }

    public static function getMimeType(int $id): string
    {
        return self::$MIME_TYPES[$id];
    }

    public static function getMimeTypeId(string $mimeType): int
    {
        return self::$MIME_TYPES[$mimeType];
    }

    public static function init(): void
    {
        self::addMimeType(0x00, "application/avro");
        self::addMimeType(0x01, "application/cbor");
        self::addMimeType(0x02, "application/graphql");
        self::addMimeType(0x03, "application/gzip");
        self::addMimeType(0x04, "application/javascript");
        self::addMimeType(0x05, "application/json");
        self::addMimeType(0x06, "application/octet-stream");
        self::addMimeType(0x07, "application/pdf");
        self::addMimeType(0x08, "application/vnd.apache.thrift.binary");
        self::addMimeType(0x09, "application/vnd.google.protobuf");
        self::addMimeType(0x0a, "application/xml");
        self::addMimeType(0x0b, "application/zip");
        self::addMimeType(0x0c, "audio/aac");
        self::addMimeType(0x0d, "audio/mp3");
        self::addMimeType(0x0e, "audio/mp4");
        self::addMimeType(0x0f, "audio/mpeg3");
        self::addMimeType(0x10, "audio/mpeg");
        self::addMimeType(0x11, "audio/ogg");
        self::addMimeType(0x12, "audio/opus");
        self::addMimeType(0x13, "audio/vorbis");
        self::addMimeType(0x14, "image/bmp");
        self::addMimeType(0x15, "image/gif");
        self::addMimeType(0x16, "image/heic-sequence");
        self::addMimeType(0x17, "image/heic");
        self::addMimeType(0x18, "image/heif-sequence");
        self::addMimeType(0x19, "image/heif");
        self::addMimeType(0x1a, "image/jpeg");
        self::addMimeType(0x1b, "image/png");
        self::addMimeType(0x1c, "image/tiff");
        self::addMimeType(0x1d, "multipart/mixed");
        self::addMimeType(0x1e, "text/css");
        self::addMimeType(0x1f, "text/csv");
        self::addMimeType(0x20, "text/html");
        self::addMimeType(0x21, "text/plain");
        self::addMimeType(0x22, "text/xml");
        self::addMimeType(0x23, "video/H264");
        self::addMimeType(0x24, "video/H265");
        self::addMimeType(0x25, "video/VP8");
        self::addMimeType(0x26, "application/x-hessian");
        self::addMimeType(0x27, "application/x-java-object");
        self::addMimeType(0x28, "application/cloudevents+json");
        self::addMimeType(0x7a, "message/x.rsocket.mime-type.v0");
        self::addMimeType(0x7b, "message/x.rsocket.accept-mime-types.v0");
        self::addMimeType(0x7c, "message/x.rsocket.authentication.v0");
        self::addMimeType(0x7d, "message/x.rsocket.tracing-zipkin.v0");
        self::addMimeType(0x7e, "message/x.rsocket.routing.v0");
        self::addMimeType(0x7f, "message/x.rsocket.composite-metadata.v0");
    }

    public static function addMimeType(int $id, string $mimeType): void
    {
        self::$MIME_TYPES[$id] = $mimeType;
        self::$MIME_TYPES[$mimeType] = $id;
    }
}

WellKnownMimeType::init();