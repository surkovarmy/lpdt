# Long proccess debug tool

### INSTALL

`composer require gbublik/lpdt:dev-master`

### Exemple

Debug long process on backend
```php
<?php
require __DIR__ . '/vendor/autoload.php';

$s = GBublik\Lpdt\Server\AsyncSocketServer::getInstance();
$s->start();

while (1){
    if ($i % 100000000 == 0) {
        $s->setStep('Группа ебак(' . $i . ')');
    }
    $s->write('Ебакаем ' . $i);

    $i++;
}
$s->stop();
```

CLI client

```php
<?php
$fp = stream_socket_client("tcp://localhost:8082", $errno, $errstr, 30);
$console = fopen('php://stdout', 'w');

if (!$fp) {
    echo "$errstr ($errno)<br />\n";
} else {
    $request = '';
    if (!isset($argv[1])) {
        $argv[1] = 'Default';
    }
    $request .= $argv[1]. (isset($argv[2]) ? ' ' . $argv[2] : '');
    $request .= "\r\nUpgrade: Cli";

    fwrite($fp, $request);
    while (!feof($fp)) {
        echo fgets($fp, 1024);
    }
    fclose($fp);
}
fclose($console);
```