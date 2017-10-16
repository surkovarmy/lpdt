<?php
namespace GBublik\Supervisor;

class Helper
{
    static public function readSocket($socket){
        while($buf = @socket_read($socket, 1024, PHP_BINARY_READ ))
            if($buf = trim($buf))
                break;

        return $buf;
    }

    static public function parseHeader($str)
    {
        $out = [];
        $lines = explode("\n", $str);
        foreach ($lines as $line) {
            $arHeader = explode(':', $line);
            $out[trim(strtolower($arHeader[0]))] = trim($arHeader[1]);
        }
        return $out;
    }

    static public function getPeerName(&$socket)
    {
        $out = [
            'address' => null,
            'port' => null
        ];
        socket_getpeername($socket, $out['address'], $out['port']);
        return $out;
    }
}