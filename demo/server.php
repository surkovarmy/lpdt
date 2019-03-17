<?php
require __DIR__ . '/../../../../vendor/autoload.php';

$s = GBublik\Lpdt\AsyncSocketServer::getInstance();
$s->start();

$i = 0;
while (1){
    $s->write('Ебакаем ' . $i);
    $i++;
}

$s->stop();