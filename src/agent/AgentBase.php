<?php

namespace GBublik\Lpdt\Agent;

abstract class AgentBase
{

    abstract public function __construct(&$socket, array $opt);

    abstract public function getType();

    abstract public function getSocket();

    abstract public function write($str);

    abstract public function read();

    abstract public function  disconnect($msg = 'Disconnect');

    abstract public function getError();
}