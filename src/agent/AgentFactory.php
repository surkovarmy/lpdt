<?php

namespace GBublik\Lpdt\Agent;

use GBublik\Lpdt\Helper;

class AgentFactory
{
    static public function create(&$socket)
    {
        $opt = Helper::parseHeader(Helper::readSocket($socket));

        switch ($opt['upgrade']) {
            case 'Console':
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