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
    fwrite(STDOUT, 'Caught user interrupt signal');

    $version = getenv('VARNISH_VERSION') ?: '6';
    $host = getenv('VARNISH_HOST') ?: '127.0.0.1';
    $port = getenv('VARNISH_PORT') ?: '6082';
    $secret = getenv('VARNISH_SECRET') ?: null;
    $servers = [
        [
            'host' => $host,
            'port' => $port,
            'version' => $version,
        ],
    ];
    if (file_exists('varnish.json')) {
    }

    foreach ($servers as $s) {
        $client = new \VarnishAdminSocket($s['host'], $s['port'], $version);
        if ($secret) {
            $client->set_auth($secret);
        }

        try {
            $client->connect();
            $client->quit();
        } catch (\Exception $e) {
            fwrite(STDOUT, $e->getMessage());
        }
    }
});
$loop->run();
