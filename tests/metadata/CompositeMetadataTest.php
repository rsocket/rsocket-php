<?php


namespace metadata;


use PHPUnit\Framework\TestCase;
use RSocket\metadata\CompositeMetadata;
use RSocket\metadata\RoutingMetadata;

class CompositeMetadataTest extends TestCase
{

    public function testRouting(): void
    {
        $routingKey = "com.example.UserService";
        $routingMetadata = new RoutingMetadata($routingKey);
        $compositeMetadata = CompositeMetadata::fromEntries($routingMetadata);
        $bytes = $compositeMetadata->toUint8Array();
        $compositeMetadata2 = CompositeMetadata::fromU8Array($bytes);
        foreach ($compositeMetadata2->getAllEntries() as $entry) {
            $mimeType = $entry->mimeType;
            if ($mimeType === 'message/x.rsocket.routing.v0') {
                $routingMetadata2 = RoutingMetadata::fromEntry($entry);
                self::assertEquals($routingKey, $routingMetadata2->routingKey);
            }
        }

    }
}