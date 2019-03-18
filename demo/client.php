<?php
$fp = stream_socket_client("tcp://localhost:8082", $errno, $errstr, 30);
$console = fopen('php://stdout', 'w');

if (!$fp) {
    echo "$errstr ($errno)<br />\n";
} else {
    fwrite($fp, "upgrade: Console");
    while (!feof($fp)) {
        fwrite($console, fgets($fp, 1024));
    }
    fclose($fp);
}
fclose($console);
