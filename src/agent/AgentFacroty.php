<?php

namespace GBublik\Supervisor\Agent;

use GBublik\Supervisor\Config;
use GBublik\Supervisor\Helper;

class AgentFacroty
{
    static public function create(&$socket)
    {
        $opt = Helper::parseHeader(Helper::readSocket($socket));

        switch ($opt['upgrade']) {
            case 'SupervisorClient':
                $agent = new ConsoleAgent($socket, $opt);
                break;
            case 'Websocket':
                //$agent = new WebsocketAgent($socket);
                break;
        }
        if (!isset($agent))
            return null;
        if (isset($agent) && empty($agent->getError()))
            return $agent;

        $agent->write($agent->getError());
        $agent->disconnect();
        return null;
    }
}