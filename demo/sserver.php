<?php
include 'tcp/AsyncSocketServer.php';

$socketServer = new AsyncSocketServer();

$i = 0;
while (1) {
    $msg = 'Iterator: '.$i;
    $socketServer->write($msg);
    //echo $socketServer->writeLn($msg);
    $i++;
    sleep(1);
}
$socketServer->stopServer();
?>