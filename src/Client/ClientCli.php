<?php
/**
 * Created by PhpStorm.
 * User: Maxim Surkov
 * Email: surkovarmy@yandex.ru
 * Date: 19.03.2019
 */

namespace GBublik\Lpdt\Client;


class ClientCli extends ClientBase
{
    /**
     * @var bool|resource
     */
    public $console;

    /**
     * ClientCli constructor.
     */
    public function __construct()
    {
        $this->console = fopen('php://stdout', 'w');
    }

    /**
     * @param $socket
     * @param int $length
     */
    public function getData( $socket ,int $length)
    {
        fwrite($socket->socket, "Upgrade: Cli");
        while (!feof($socket->socket)) {
            fwrite($this->console, fgets($socket->socket, $length));
        }
        fclose($this->console);
    }

}