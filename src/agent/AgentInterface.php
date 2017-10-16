<?php

namespace GBublik\Supervisor\Agent;

interface AgentInterface
{

    public function __construct(&$socket, array $opt);

    public function getType();

    public function getSocket();
    /**
     * Send string to socket agent
     * @param string $str
     * @return boolean
     */
    public function write($str);

    public function read();

    public function  disconnect($msg = 'Disconnect');

    public function getError();
}