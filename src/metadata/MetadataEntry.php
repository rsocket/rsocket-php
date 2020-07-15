<?php


namespace RSocket\metadata;


class MetadataEntry
{
    public string $mimeType;
    public array $content;
    public ?int $id = null;

    public static function explicit(string $mimeType, array $content): MetadataEntry
    {
        $entry = new self();
        $entry->mimeType = $mimeType;
        $entry->content = $content;
        return $entry;
    }

    public static function wellKnown(int $id, string $mimeType, array $content): MetadataEntry
    {
        $entry = new self();
        $entry->id = $id;
        $entry->mimeType = $mimeType;
        $entry->content = $content;
        return $entry;
    }


}