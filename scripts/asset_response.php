<?php
require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;

$client = new Client(['base_uri' => 'http://127.0.0.1:8000', 'http_errors' => false]);
$path = '/filament/assets/app.css?id=051ae0c568738c610482ba758aff7ae4';
$res = $client->request('GET', $path);
echo 'STATUS: ' . $res->getStatusCode() . PHP_EOL;
echo 'HEADERS:' . PHP_EOL;
foreach ($res->getHeaders() as $name => $values) {
    echo $name . ': ' . implode(', ', $values) . PHP_EOL;
}
echo 'BODY_SNIPPET:' . PHP_EOL;
echo substr((string) $res->getBody(), 0, 500) . PHP_EOL;
