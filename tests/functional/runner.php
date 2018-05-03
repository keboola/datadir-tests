<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

require_once 'vendor/autoload.php';

$finder = new Finder();
$finder->in(__DIR__)->directories()->depth(0);

$log = function ($message): void {
    echo 'datadir-test-runner: ' . $message . PHP_EOL;
};

/** @var \Symfony\Component\Finder\SplFileInfo $dir */
foreach ($finder as $dir) {
    $log($dir->getPathname());
    $process = new Process(sprintf(
        'php src/datadir-tests %s %s',
        $dir->getPathname(),
        __DIR__ . '/dummy-app.php'
    ));
    $process->run();
    $out = $process->getOutput();
    $out .= $process->getErrorOutput();
    $expected = file_get_contents($dir->getPathname() . '/expected-output');
    if (!($out === $expected)) {
        $log("Expectation failed");
        $log("Expected:");
        echo $expected;
        $log("Got:");
        echo $out;
    } else {
        $log("Passed");
    }
}
echo PHP_EOL;
