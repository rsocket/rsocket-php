<?php


namespace RSocket\core;


class StreamIdSupplier
{
    private static int $MASK = 0x7FFFFFFF;
    private int $streamId;

    /**
     * StreamIdSupplier constructor.
     * @param int $streamId
     */
    public function __construct(int $streamId)
    {
        $this->streamId = $streamId;
    }

    public function nextStreamId(array $streamIds): int
    {
        do {
            $this->streamId += 2;
            $nextStreamId = $this->streamId & self::$MASK;
        } while ($this->streamId === 0 || array_key_exists($nextStreamId, $streamIds));
        return $nextStreamId;
    }

    public static function clientSupplier(): StreamIdSupplier
    {
        return new StreamIdSupplier(-1);
    }

    public static function serverSupplier(): StreamIdSupplier
    {
        return new StreamIdSupplier(0);
    }


}