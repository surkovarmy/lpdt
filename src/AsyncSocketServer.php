<?php
/**
 * Асинхронный сокет сервер. Создает сокет в неблокирующем решиме.
 * Не требуются внешние библиотеки. extension_loaded('sockets') == true
 *
 * Задача: в консольных скриптах выполняющиеся долгое время,
 * важно контролировать процесс выполнения скрипта,
 * а и иногда управлять процессом прямо в процессе его работы
 *
 * Решение: Скрипт создает сокет сервер, и предлагает 1 метод write
 * для рассылки оповещений всем подключившимся к сокету. Так же содержит (решение)
 * дополнительные инструменты для работы с сокет сервером.
 * Сервер работает одновременно в двух режимах:
 *   1. WebSocket для мониторинга оператором выполнение процесса с веб странички
 *   2. Консольный, не шифрованный, режим для администрирования с локального хоста.
 */
class AsyncSocketServer
{
    /** @var null Сокет сервера */
    protected $socket = null;

    /** @var array Список клиентов */
    protected $clients = [];

    /** @var string Хост , localhost по умолчанию  */
    protected $host = 'localhost';

    /** @var int Порт, 8082 по умолчанию */
    protected $port = 8082;

    /**
     * AsyncSocketServer constructor.
     * @param string $host
     * @param int $port
     * @throws Exception
     */
    public function __construct($host = null, $port = null)
    {
        if(!extension_loaded('sockets')) {
            throw new Exception('WebSockets UNAVAILABLE');
        }
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

                if ($error > 0) echo $this->writeLn("Error: " . socket_strerror($error));

                $headers = $this->parseHeaders($this->read($conn));
                if ($headers['sec-websocket-key']) {
                    $hash = base64_encode(SHA1($headers['sec-websocket-key']."258EAFA5-E914-47DA-95CA-C5AB0DC85B11", true));
                    socket_write($conn,
                        "HTTP/1.1 101 Switching Protocols
Upgrade: websocket
Connection: Upgrade
Sec-WebSocket-Accept: " . $hash ."\r\n\r\n");
                }
                $info = $this->getInfo($conn);
                if ($error = socket_last_error($conn)) {
                    echo $this->writeLn("Error connection: " . $info['address'].':'.$info['port']. " ".socket_strerror($error));
                } else {
                    echo $this->writeLn("Connection " . $info['address'].':'.$info['port']);
                    $this->clients[] = $conn;
                }
            }
        }
    }

    /**
     * Останавливает работу сервера
     */
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

    /**
     * Читает сообщение от клиента
     * @param $sock
     * @return string
     */
    protected function read($sock){
            while($buf = @socket_read($sock, 1024, PHP_BINARY_READ ))
            if($buf = trim($buf))
                break;

        return $buf;
    }

    /**
     * Пишит строку всем клиентам
     * @param string $str
     * @param bool $isTick Если true сначало выполнит $this->tick(). По умолчанию true
     */
    public function write($str, $isTick = true) {
        if ($isTick) $this->tick();
        $str = $this->writeLn($str);
        foreach ($this->clients as $key=>$client) {
            @socket_write($client, $this->hybi10Encode($str, 'text', false));
            $error = socket_last_error($client);
            $info = $this->getInfo($client);
            if ($error > 0) {
                echo $this->writeLn("Disconnect: ".$info['address'] . ":" . $info['port']);
                socket_close($client);
                unset($this->clients[$key]);
            }
        }
    }

    /**
     * Разберает строку с загаловками на массив
     * @param $str
     * @return array
     */
    protected function parseHeaders($str)
    {
        $out = [];
        $lines = explode("\n", $str);
        foreach ($lines as $line) {
            $arHeader = explode(':', $line);
            $out[trim(strtolower($arHeader[0]))] = trim($arHeader[1]);
        }
        return $out;
    }

    /**
     * Кодирует $payload для отправки по протаколу web-socket
     * @param $payload
     * @param string $type
     * @param bool $masked
     * @return string
     */
    private function hybi10Encode($payload, $type = 'text', $masked = true)
    {
        $frameHead = array();
        $frame = '';
        $payloadLength = strlen($payload);

        switch($type)
        {
            case 'text':
                // first byte indicates FIN, Text-Frame (10000001):
                $frameHead[0] = 129;
                break;

            case 'close':
                // first byte indicates FIN, Close Frame(10001000):
                $frameHead[0] = 136;
                break;

            case 'ping':
                // first byte indicates FIN, Ping frame (10001001):
                $frameHead[0] = 137;
                break;

            case 'pong':
                // first byte indicates FIN, Pong frame (10001010):
                $frameHead[0] = 138;
                break;
        }

        // set mask and payload length (using 1, 3 or 9 bytes)
        if($payloadLength > 65535)
        {
            $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 255 : 127;
            for($i = 0; $i < 8; $i++)
            {
                $frameHead[$i+2] = bindec($payloadLengthBin[$i]);
            }
            // most significant bit MUST be 0 (close connection if frame too big)
            if($frameHead[2] > 127)
            {
                echo $this->writeLn("Error v funktsii");
                return false;
            }
        }
        elseif($payloadLength > 125)
        {
            $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 254 : 126;
            $frameHead[2] = bindec($payloadLengthBin[0]);
            $frameHead[3] = bindec($payloadLengthBin[1]);
        }
        else
        {
            $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
        }
        // convert frame-head to string:
        foreach(array_keys($frameHead) as $i)
        {
            $frameHead[$i] = chr($frameHead[$i]);
        }
        $mask = array();
        if($masked === true)
        {
            // generate a random mask:
            for($i = 0; $i < 4; $i++)
            {
                $mask[$i] = chr(rand(0, 255));
            }

            $frameHead = array_merge($frameHead, $mask);
        }
        $frame = implode('', $frameHead);

        for($i = 0; $i < $payloadLength; $i++)
        {
            $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
        }
        return $frame;
    }
}