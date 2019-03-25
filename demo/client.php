<?php
require __DIR__ . '/../../../../vendor/autoload.php';

$client = new GBublik\Lpdt\Client\Client();
$client->start();
$client->getData();
$client->close();
