<?php

declare(strict_types=1);

$datadir = getenv('KBC_DATADIR');
$config = json_decode((string) file_get_contents($datadir . '/config.json'), true);
if (isset($config['message'])) {
    echo $config['message'];
}
if (isset($config['code'])) {
    exit($config['code']);
}
exit(0);
