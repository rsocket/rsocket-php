<?php


namespace RSocket\metadata;


class RoutingMetadata extends TaggingMetadata
{
    public string $routingKey;
    public ?array $extraTags = null;

    public function __construct(string $routingKey, ?array $extraTags = null)
    {
        $this->routingKey = $routingKey;
        $this->extraTags = $extraTags;
        $tags = [];
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

    public function getRoutingParts(): array
    {
        if (strpos($this->routingKey, '.') !== false) {
            $pos = strrpos($this->routingKey, ".", -1);
            $serviceName = substr($this->routingKey, 0, $pos);
            $method = substr($this->routingKey, $pos + 1);
            return [
                "service" => $serviceName,
                "method" => $method
            ];
        }
        return [];
    }
}