<?php
require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;

$client = new Client(['base_uri' => 'http://127.0.0.1:8000', 'http_errors' => false]);
$paths = [
    '/filament/assets/app.css?id=051ae0c568738c610482ba758aff7ae4',
    '/filament/assets/app.js?id=e520ab59823aaadda34c7c40555db9f2',
    '/livewire/livewire.js?id=90730a3b0e7144480175',
];
foreach ($paths as $path) {
    $res = $client->request('GET', $path);
    echo "$path => " . $res->getStatusCode() . "\n";
    echo substr((string) $res->getBody(), 0, 200) . "\n---\n";
}
