<?php
/**
 * Created by PhpStorm.
 * User: Maxim Surkov
 * Email: surkovarmy@yandex.ru
 * Date: 19.03.2019
 */

namespace GBublik\Lpdt\Client;


class ClientConsole extends ClientBase
{
    public $console;

    public function __construct()
    {
        $this->console = fopen('php://stdout', 'w');
    }

    public function getData( $socket ,int $length)
    {
        fwrite($socket->socket, "upgrade: Console");
        while (!feof($socket->socket)) {
            fwrite($this->console, fgets($socket->socket, $length));
        }
        fclose($this->console);
    }

}