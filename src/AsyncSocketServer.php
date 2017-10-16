<?php

namespace GBublik\Supervisor;

use GBublik\Supervisor\Agent\AgentFacroty;

/**
 * Class object for create noblock socket server
 * @package BV\ROoHP
 */
class AsyncSocketServer
{
    /** @var resource Socket server */
    protected $socket = null;

    /** @var Agent\AgentInterface[] Array of client objects */
    protected $agents = [];

    /** @var string Socket server name  */
    protected $host = null;

    /** @var int Socket server port */
    protected $port = null;

    protected $error = null;

    protected $isRun = false;

    /**
     * AsyncSocketServer constructor.
     * @param string $host
     * @param int $port
     */
    public function __construct($host = 'localhost', $port = 8082)
    {
        $this
            ->checkDependency()
            ->setHost($host)
            ->setPort($port);
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param int $port
     * @return AsyncSocketServer
     */
    public function setPort($port = 8082)
    {
        $this->port = (int)$port;
        return $this;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     * @return AsyncSocketServer
     */
    public function setHost($host = 'localhost')
    {
        $this->host = (string)$host;
        return $this;
    }

    /**
     * Run server
     */
    public function start()
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_bind($this->socket , $this->getHost(), $this->getPort());
        socket_listen($this->socket);
        socket_set_nonblock($this->socket);
        $this->createFatalErrorBySocket($this->socket);

        $this->isRun = true;
        return $this->isRun();
    }

    public function stop()
    {
        if ($this->isRun) {
            socket_close($this->socket);
            $this->isRun = false;
        }
        return true;
    }

    /**
     * Return True is server started
     * @return bool
     */
    public function isRun()
    {
        return $this->isRun;
    }

    public function listnerNewConnections()
    {
        if ( $conn = socket_accept($this->socket)) {
            if ($error = $this->getSocketError($conn)) {
                //Todo тут должна быть рассылка которой еще нет
            } else {
                $this->subscribe($conn);
            }
        }
    }

    /**
     * @return Agent\AgentInterface[]
     */
    public function getAgents()
    {
        return $this->agents;
    }

    public function write($str, $types = [])
    {
        foreach ($this->getAgents() as $agent) {
            if (empty($types) || in_array($agent->getType(), $types))
                $agent->write($str);
        }
    }

    protected function subscribe(&$socket) {
        if ($agent = AgentFacroty::create($socket)) {
            $this->agents[] = &$agent;
        }
    }

    protected function checkDependency()
    {
        if(!extension_loaded('sockets')) {
            throw new Exception('Extension sockets is not installed');
        }
        return $this;
    }

    protected function getSocketError(&$socket)
    {
        $error = socket_last_error($socket);
        if ($error > 0) return socket_strerror($error);
        return null;
    }

    protected function createFatalErrorBySocket(&$socket)
    {
       if ($error = $this->getSocketError($socket)) {
           throw new \Exception($error);
       }
    }
}