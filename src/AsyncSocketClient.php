<?php
/**
 * Created by PhpStorm.
 * User: Maxim Surkov
 * Email: surkovarmy@yandex.ru
 * Date: 19.03.2019
 */

namespace GBublik\Lpdt;

use GBublik\Lpdt\Client\ClientFactory;
use GBublik\Lpdt\General\AsyncSocketClientException;

class AsyncSocketClient
{
    public $socket = null;
    public $type_connect = 'console';
    public $client;

    public function __construct(string $host = 'localhost', int $port = 8082, string $type = 'tcp')
    {
        $this->socket = stream_socket_client($type."://".$host.":".$port, $errno, $errstr, 30);
        if ($errno) {
            throw new AsyncSocketClientException('Extension stream socket error '.$errno.'. Error to string: '.$errstr);
        };
    }

    public function start( string $type_connect = 'console' ) {
        $this->client = ClientFactory::create($type_connect);
    }

    public function getData(int $length = 1024) {
        $this->client->getData( $this, $length);
    }


}