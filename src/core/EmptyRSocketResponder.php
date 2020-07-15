<?php


namespace RSocket\core;

use RSocket\AbstractRSocket;
use RSocket\RSocket;

class EmptyRSocketResponder extends AbstractRSocket
{
    private static ?RSocket $INSTANCE = null;

    public static function getInstance(): RSocket
    {
        if (is_null(self::$INSTANCE)) {
            self::$INSTANCE = new self();
        }
        return self::$INSTANCE;
    }

}