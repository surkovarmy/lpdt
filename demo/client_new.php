<?php
require __DIR__ . '/../../../../vendor/autoload.php';

$client = new AsyncSocketClient();
$client->start();
$client->getData();
$client->close();
