<?php

namespace GBublik\Lpdt\Client;

abstract class ClientBase
{
    /**
     * @param $socket
     * @param int $length
     * @return mixed
     */
    abstract public function getData($socket, int $length);
}