<?php

namespace GBublik\Lpdt\Server\Agent;

use GBublik\Lpdt\Server\Config;
use GBublik\Lpdt\Server\Request;

/** Base client agent */
abstract class AgentBase
{
    /** @var resource Client socket */
    protected $socket = null;

    /** @var Request */
    protected $request;

    /** @var Config */
    protected $config;

    public function __construct(&$socket, Request $request, Config $config)
    {
        $this->socket = $socket;
        $this->request = $request;
        $this->config = $config;
    }

    /**
     * @param array $serverStatistic
     * @return mixed
     * @throws AgentException
     */
    abstract public function tick($serverStatistic = []);

    /**
     * @param $str
     * @return mixed
     * @throws AgentException
     */
    abstract public function write($str, $serverInfo);

    abstract public function getSocket();

    abstract public function disconnect($msg = 'Disconnect');
}