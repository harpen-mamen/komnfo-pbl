<?php
require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;

$servers = [
    '8000' => 'http://127.0.0.1:8000',
    '8001' => 'http://127.0.0.1:8001',
    '8002' => 'http://127.0.0.1:8002',
];
$paths = [
    '/admin/login',
    '/filament/assets/app.css?id=051ae0c568738c610482ba758aff7ae4',
    '/filament/assets/app.js?id=e520ab59823aaadda34c7c40555db9f2',
    '/livewire/livewire.js?id=90730a3b0e7144480175',
];

foreach ($servers as $port => $base) {
    echo "SERVER $port ($base)\n";
    $client = new Client(['base_uri' => $base, 'cookies' => true, 'http_errors' => false]);
    foreach ($paths as $path) {
        $res = $client->request('GET', $path);
        echo sprintf("  %s => %d\n", $path, $res->getStatusCode());
    }
    echo "---\n";
}
