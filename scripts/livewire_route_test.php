<?php
require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;

$client = new Client([
    'base_uri' => 'http://127.0.0.1:8000',
    'cookies' => true,
    'http_errors' => false,
]);

$paths = [
    '/livewire/message/foo',
    '/livewire/message/filament.core.auth.login',
];

foreach ($paths as $path) {
    $res = $client->request('GET', $path);
    echo "GET $path => " . $res->getStatusCode() . "\n";
    echo substr((string) $res->getBody(), 0, 300) . "\n---\n";

    $res2 = $client->request('POST', $path, [
        'headers' => [
            'X-Requested-With' => 'XMLHttpRequest',
        ],
        'form_params' => ['foo' => 'bar'],
    ]);
    echo "POST $path => " . $res2->getStatusCode() . "\n";
    echo substr((string) $res2->getBody(), 0, 300) . "\n===\n";
}
