<?php

declare(ticks=1);

require_once 'vendor/autoload.php';
require_once 'VarnishAdminSocket.php';

if (!defined('SIGINT')) {
    fwrite(STDERR, 'Not supported on your platform (ext-pcntl missing or Windows?)' . PHP_EOL);
    exit(1);
}

$loop = \React\EventLoop\Factory::create();

$loop->addSignal(SIGHUP, function (int $signal) {
    fwrite(STDOUT, 'Caught user interrupt signal:' . $signal);
    include 'common.php';
});
$loop->run();
