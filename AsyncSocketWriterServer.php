<?php
/**
 * Создает асинхронный сокет сервер
 * Фишки:
 *  1. Раздает информацию всем подключившимя клиентам
 */
class AsyncSocketWriterServer
{
    /** @var null Сокет сервера */
    protected $socket = null;

    /** @var array Список клиентов */
    protected $clients = [];

    protected $host = 'localhost';

    protected $port = 8082;

    /**
     * AsyncSocketServer constructor.
     * @param string $host
     * @param int $port
     */
    public function __construct($host = null, $port = null)
    {
        if (!empty($host)) {
            $this->host = $host;
        }
        if (!empty($port)) {
            $this->port = $port;
        }
        $this->startServer();
    }

    /**
     * Стартует сервер
     * @throws Exception
     */
    public function startServer() {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_bind($this->socket , $this->host, $this->port);
        socket_listen($this->socket);
        socket_set_nonblock($this->socket);
        $error = socket_last_error($this->socket);
        if ($error > 0) throw new Exception(socket_strerror($error));
    }

    /**
     * Запускается на каждом тике в бесконечном цикле извне
     */
    public function tick() {
        $this->newClient();
    }

    /**
     * Пишит строку всем клиентам
     * @param string $str
     */
    public function write($str) {
        $str = $this->writeLn($str);
        foreach ($this->clients as $key=>$client) {
            @socket_write($client, $str, strlen($str));
            $error = socket_last_error($client);
            if ($error > 0) {
                $info = $this->getInfo($client);
                socket_close($client);
                unset($this->clients[$key]);
                echo 'Client ' . $info['address'] . ':' . $info['port']. ': '.socket_strerror($error);
            }
        }
    }

    /**
     * Возвращает информацию о клиенте
     * @param $client
     * @return array
     */
    protected function getInfo($client)
    {
        $out = [
            'address' => null,
            'port' => null
        ];
        socket_getpeername($client, $out['address'], $out['port']);
        return $out;
    }

    /**
     * Проверяет новые соединения
     */
    protected function newClient()
    {
        if ( $conn = socket_accept($this->socket)) {
            $error = socket_last_error($conn);
            if ($error > 0) {
                echo $this->writeLn("Error: ". socket_strerror($error));
            } else {
                $this->clients[] = $conn;
                $info = $this->getInfo($conn);
                echo $this->writeLn("Connection " . $info['address'].':'.$info['port']);
            }
        }
    }

    public function stopServer(){
        foreach ($this->clients as $client) {
            socket_close($client);
        }
        socket_shutdown($this->socket);
    }

    /**
     * Добвляет строке $str переход на новую строку
     * @param string $str
     * @return string
     */
    public function writeLn($str)
    {
        return $str."\r\n";
    }
}