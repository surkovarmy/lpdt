<?php
/**
 * Created by PhpStorm.
 * User: Maxim Surkov
 * Email: surkovarmy@yandex.ru
 * Date: 19.03.2019
 */

namespace GBublik\Lpdt\Client;

use \GBublik\Lpdt\Client\ClientException;

class Client
{
    /**
     * @var bool|null|resource
     */
    public $socket = null;
    /**
     * @var string
     */
    public $type_connect = 'console';
    /** @var  ClientConsole*/
    public $client;

    public function __construct(string $host = 'localhost', int $port = 8082, string $type = 'tcp')
    {
        $this->socket = stream_socket_client($type."://".$host.":".$port, $errno, $errstr, 30);
        if ($errno) {
            throw new ClientException('Extension stream socket error '.$errno.'. Error to string: '.$errstr);
        };
    }

    /**
     * @param string $type_connect
     */
    public function start( string $type_connect = 'cli' ) {
        $this->client = ClientFactory::create($type_connect);
    }

    /**
     * @param int $length
     */
    public function getData(int $length = 1024) {
        $this->client->getData( $this, $length);
    }

    /**
     * close connect
     */
    public function close()
    {
        fclose($this->socket);
    }
}