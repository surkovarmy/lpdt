<?php

namespace GBublik\Lpdt\Server;

use GBublik\Lpdt\Server\Agent;
use GBublik\Lpdt\Server\Agent\AgentFactory;

declare(ticks=100000);

/**
 * Class object for create noblock socket server
 * @package GBublik\Lpdt
 */
class AsyncSocketServer
{
    protected $stackSteps = [];

    /** @var resource Socket server */
    protected $socket = null;

    /** @var array clients poll */
    protected $clients = [];

    /** @var string Socket server name */
    protected $host = null;

    /** @var int Socket server port */
    protected $port = null;

    protected $isRun = false;

    /** @var resource */
    protected $console;

    /** @var int */
    protected $tickCounter = 0;

    /** @var self */
    protected static $instance;

    /** @var Config */
    protected $config;

    protected $serverInfo = [
        'start_time' => null,
        'step' => null,
        'executed_step' => []
    ];

    protected function __construct($host = null, $port = null)
    {
        $this->console = fopen('php://stdout', 'w');
        $this->config = new Config();
        $this->setHost($host ?: $this->config['default_host'])
            ->setPort($port ?: $this->config['default_port']);

        $this->serverInfo['start_time'] = new \DateTime();
    }

    public static function getInstance($host = null, $port = null)
    {
        if (empty(self::$instance)) self::$instance = new self($host, $port);
        return self::$instance;
    }

    public function __destruct()
    {
        @$this->stop();
        @fclose($this->console);
    }

    /**
     * Run server
     */
    public function start()
    {
        if (!$this->config['disabled']) {
            $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            socket_bind($this->socket, $this->getHost(), $this->getPort());
            socket_listen($this->socket);
            socket_set_nonblock($this->socket);
            $this->createFatalErrorBySocket($this->socket);

            $this->isRun = true;

            $this->writeConsole("LPDT: Run debug server (" . $this->getHost() . ":" . $this->getPort() . ")");

            register_tick_function([&self::$instance, 'tickHandler'], true);
        } else {
            $this->writeConsole("LPDT: Debug server is disabled (" . $this->getHost() . ":" . $this->getPort() . ")");
        }
        return self::$instance;
    }

    public function stop()
    {
        if ($this->isRun) {
            socket_close($this->socket);
            $this->isRun = false;
            $this->writeConsole("Stop debug server");
            unregister_tick_function([&self::$instance, 'tickHandler']);
        }
        return self::$instance;
    }

    /**
     * Return True is server started
     * @return bool
     */
    public function isRun()
    {
        return $this->isRun;
    }

    public function listenerNewConnections()
    {
        if ($socket = socket_accept($this->socket)) {
            if ($agent = AgentFactory::create($socket, $this->config)) {
                socket_getpeername($socket, $host, $port);
                $this->clients[] = [
                    'host' => $host ?: '<empty>',
                    'port' => $port ?: '<empty>',
                    'agent' => &$agent
                ];
                $this->writeConsole("LPDT: New connection (" . ($host ?: '<empty>') . ":" . ($port ?: '<empty>') . ")");
            }
        }
    }

    function tickHandler()
    {
        if (!$this->isRun()) return;
        $this->tickCounter++;
        if ($this->tickCounter > $this->config['tick_scale']) {
            $this->listenerNewConnections();
            foreach ($this->clients as $key=>$client) {
                try {
                    /** @var Agent\AgentBase $agent */
                    $agent = &$client['agent'];
                    $agent->tick($this->serverInfo);
                } catch (Agent\AgentException $e) {
                    $this->writeConsole(
                        'LPDT: ' . $e->getMessage() . ' (' .
                        $this->clients[$key]['host']  .
                        $this->clients[$key]['port'] . ')'
                    );
                    unset($this->clients[$key]);
                }
            }
            fwrite($this->console, $this->loading());
            $this->tickCounter = 0;
        }
    }

    public function write($str)
    {
        if (!$this->isRun()) return;

        foreach ($this->clients as $key => $client) {
            try {
                /** @var Agent\AgentBase $agent */
                $agent = $client['agent'];
                $agent->write($str, $this->serverInfo);
            } catch (Agent\AgentException $e) {
                $this->writeConsole(
                    'LPDT: ' . $e->getMessage() . ' (' .
                    $this->clients[$key]['host']  .
                    $this->clients[$key]['port'] . ')'
                );
                unset($this->clients[$key]);
            }
        }
    }

    protected function writeConsole($str)
    {
        fwrite($this->console, "\r" . $str . "\n");
    }

    /**
     * @return Agent\AgentBase[]
     */
    public function getAgents()
    {
        return $this->clients;
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

    public function setStep($str) {
        if ($str != $this->serverInfo['step']) {
            if ($this->serverInfo['step']) $this->serverInfo['executed_step'][] = $this->serverInfo['step'];
            $this->serverInfo['step'] = $str;
            $this->write('start');
        }
    }

    public function getStep() {
        return $this->serverInfo['tag'];
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
     * @return self
     */
    public function setPort($port)
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
     * @return self
     */
    public function setHost($host)
    {
        $this->host = (string)$host;
        return $this;
    }

    protected function loading()
    {
        static $current;
        $response = '';

        switch ($current)
        {
            case 0:
                $response = '|';
                break;
            case 1:
                $response = '/';
                break;
            case 2:
                $response = '-';
                break;
            case 3:
                $response = '\\';
                break;
            case 4:
                $response = '-';
                break;
        }
        if ($current == 4) $current = 0;
        else $current++;
        return "\r" . $response;
    }
}