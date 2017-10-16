<?php
namespace GBublik\Supervisor\Agent;

class ConsoleAgent implements AgentInterface
{
    protected $socket = null;

    protected $error = null;

    public function __construct(&$socket, array $opt)
    {
        $this->socket = $socket;
    }

    public function getType()
    {
        return 'console';
    }

    public function read()
    {
        while ($buf = @socket_read($this->socket, 1024, PHP_BINARY_READ))
            if ($buf = trim($buf))
                break;

        return $buf;
    }

    public function write($str)
    {
        @socket_write($this->socket, $this->addrn($str));
    }

    public function getSocket()
    {
        return $this->socket;
    }

    public function disconnect($msg = 'Disconnect')
    {
        $this->write($this->addrn($msg));
        socket_close($this->socket);
    }

    protected function addrn($str)
    {
        return $str . "\r\n";
    }

    public function getError()
    {
        return $this->error;
    }
}