<?php
/**
 * Created by PhpStorm.
 * User: Maxim Surkov
 * Email: surkovarmy@yandex.ru
 * Date: 19.03.2019
 */

namespace GBublik\Lpdt\Client;

class ClientFactory
{
    static public function create($type_connect)
    {
        switch ($type_connect) {
            case 'console':
                return new ClientConsole();
                break;
        }

    }
}