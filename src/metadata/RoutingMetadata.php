<?php


namespace RSocket\metadata;


class RoutingMetadata extends TaggingMetadata
{
    public string $routingKey;
    public ?array $extraTags = null;

    public function __construct(string $routingKey, ?array $extraTags)
    {
        $this->routingKey = $routingKey;
        $this->extraTags = $extraTags;
        $tags = array();
        $tags[] = $routingKey;
        if ($extraTags !== null) {
            array_push($tags, ...$extraTags);
        }
        parent::__construct("message/x.rsocket.routing.v0", $tags);
    }

    public static function fromEntry(MetadataEntry $entry): RoutingMetadata
    {
        $taggingMetadata = TaggingMetadata::fromEntry($entry);
        $tags = $taggingMetadata->tags;
        $len = count($tags);
        if ($len === 0) {
            return new RoutingMetadata('', null);
        }
        if ($len === 1) {
            return new RoutingMetadata($tags[0], null);
        }
        return new RoutingMetadata($tags[0], array_slice($tags, 1));
    }
}