<?php


namespace RSocket\io;


interface Closeable
{
    public function close(): void;
}