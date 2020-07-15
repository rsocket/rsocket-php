<?php


namespace RSocket\routing;


class JsonSupport
{
    public function loadFromJson($data): void
    {
        $obj = $data;
        if (is_string($data)) {
            $obj = json_decode($data, true);
        }
        foreach ($obj as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function toJson(): string
    {
        return json_encode($this);
    }
}