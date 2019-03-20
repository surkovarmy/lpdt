<?php

namespace GBublik\Lpdt\Client;

abstract class ClientBase
{

    abstract public function getData($socket, int $length);

}