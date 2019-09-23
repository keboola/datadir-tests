<?php

declare(strict_types=1);

$datadir = getenv('KBC_DATADIR');
$config = json_decode(file_get_contents($datadir . '/config.json'), true);
if (isset($config['message'])) {
    echo $config['message'];
}
if (isset($config['stderr'])) {
    fwrite(STDERR, $config['stderr']);
}

if (isset($config['code'])) {
    exit($config['code']);
}
exit(0);
