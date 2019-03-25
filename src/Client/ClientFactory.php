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
    /**
     * @param $type_connect
     */
    static public function create($type_connect)
    {
        switch ($type_connect) {
            case 'cli':
                return new ClientCli();
                break;
        }

    }
}