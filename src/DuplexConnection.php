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

    protected float $_availability = 1.0;

    abstract public function write(array $frameArray): void;

    abstract public function init(): void;

    public function availability(): float
    {
        return $this->_availability;
    }

}
