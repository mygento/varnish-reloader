<?php

$version = getenv('VARNISH_VERSION') ?: '6';
$host = getenv('VARNISH_HOST') ?: '127.0.0.1';
$port = getenv('VARNISH_PORT') ?: '6082';
$secret = getenv('VARNISH_SECRET') ?: null;
$vclFile = getenv('VCL_PATH') ?: '/app/current.vcl';
$servers = [
    [
        'host' => $host,
        'port' => $port,
        'version' => $version,
    ],
];
$file = getenv('VARNISH_HOSTS_FILE') ?: '/app/varnish.list';
if (file_exists($file)) {
    $servers = [];
    $fileContent = file_get_contents($file);
    $lines = explode(PHP_EOL, $fileContent);
    foreach ($lines as $line) {
        if (!trim($line)) {
            continue;
        }
        $sp = explode(':', $line);
        $servers[] = [
            'host' => $sp[0],
            'port' => $sp[1] ?? $port,
        ];
    }
}

$vclContent = file_get_contents($vclFile);
if (!$vclContent) {
    fwrite(STDERR, 'VCL not found or empty' . PHP_EOL);

    return;
}

$vcl = str_replace(PHP_EOL, '\n', addcslashes($vclContent, '"\\'));

$vclName = 'vcl_' . date('YYmmddhhiiss');

foreach ($servers as $s) {
    $client = new \VarnishAdminSocket($s['host'], $s['port'], $version);
    if ($secret) {
        $client->set_auth($secret . "\n");
    }

    try {
        $client->connect();
        $code = null;
        $response = $client->command('vcl.inline ' . $vclName . ' "' . $vcl . '"', $code);
        var_dump($response);
        sleep(1);
        $response = $client->command('vcl.use  ' . $vclName, $code);
        var_dump($response);
        $client->quit();
    } catch (\Exception $e) {
        fwrite(STDOUT, $e->getMessage());
    }
}