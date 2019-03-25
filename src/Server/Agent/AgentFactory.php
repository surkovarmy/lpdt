<?php
namespace GBublik\Lpdt\Server\Agent;

use GBublik\Lpdt\Server\Config;
use GBublik\Lpdt\Server\Request;

class AgentFactory
{
    static public function create(&$socket, Config $config)
    {
        while($buf = @socket_read($socket, 1024, PHP_BINARY_READ ))
            if($buf = trim($buf))
                break;

        $request = new Request($buf);
        $agentName = ucfirst($request->getHeader('upgrade'));
        $className = 'GBublik\\Lpdt\\Server\\Agent\\' . $agentName;
        if (!empty($agentName) && class_exists($className)) {
            return new $className($socket, $request, $config);
        }
        return null;
    }
}