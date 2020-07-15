<?php


namespace RSocket;


use RSocket\io\Closeable;
use RSocket\io\Availability;

abstract class DuplexConnection implements Closeable, Availability
{
    /**
     * @var callable receive handler
     */
    public $receiveHandler;
    /**
     * @var callable close handler
     */
    public $closeHandler;

    abstract public function write(array $frameArray): void;

    abstract public function init(): void;


}
