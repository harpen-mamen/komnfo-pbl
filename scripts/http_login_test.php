<?php
require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;

$client = new Client(['base_uri' => 'http://127.0.0.1:8000', 'cookies' => true, 'allow_redirects' => ['track_redirects' => true]]);

try {
    $res = $client->get('/admin/login');
    echo "GET /admin/login - " . $res->getStatusCode() . "\n";

    // parse csrf token: try hidden input first, then meta tag used by Livewire
    $body = (string) $res->getBody();
    if (preg_match('/name="_token" value="([^"]+)"/', $body, $m)) {
        $token = $m[1];
        echo "Found csrf token in hidden input\n";
    } elseif (preg_match('/<meta[^>]*name=[\"\']csrf-token[\"\'][^>]*content=[\"\']([^\"\']+)[\"\'][^>]*>/i', $body, $m)) {
        $token = $m[1];
        echo "Found csrf token in meta tag\n";
    } else {
        echo "No csrf token found\n";
        // Dump a snippet of the body for debugging
        $snippet = substr($body, 0, 5000);
        file_put_contents(__DIR__ . '/../http_login_debug.html', $snippet);
        echo "Wrote debug HTML to http_login_debug.html (first 5000 chars)\n";
        exit(1);
    }

    // Attempt Livewire form submission by parsing wire:initial-data JSON
    if (preg_match('/wire:initial-data="([^"]+)"/i', $body, $m)) {
        $raw = html_entity_decode($m[1]);
        $initial = json_decode($raw, true);
        if ($initial && isset($initial['serverMemo'])) {
            $serverMemo = $initial['serverMemo'];
            // set form data
            $serverMemo['data']['email'] = 'admin.kecamatan@sipdt.test';
            $serverMemo['data']['password'] = 'password';
            $serverMemo['data']['remember'] = true;

            $fingerprint = $initial['fingerprint'] ?? ['name' => 'filament.core.auth.login'];

            $livewirePayload = [
                'fingerprint' => json_encode($fingerprint),
                'serverMemo' => json_encode($serverMemo),
                'updates' => json_encode([
                    ['type' => 'callMethod', 'payload' => ['authenticate', []]],
                ]),
            ];

            $res3 = $client->post('/livewire/message/' . $fingerprint['name'], [
                'headers' => [
                    'X-CSRF-TOKEN' => $token,
                    'X-Requested-With' => 'XMLHttpRequest',
                ],
                'form_params' => $livewirePayload,
            ]);

            echo "Livewire POST - " . $res3->getStatusCode() . "\n";
            echo "Livewire body snippet: " . substr((string)$res3->getBody(), 0, 1000) . "\n";
            // show redirect history if any
            $history = $res3->getHeader('X-Guzzle-Redirect-History');
            echo "Redirect history: " . json_encode($history) . "\n";
            exit(0);
        } else {
            echo "Could not parse Livewire initial data\n";
            exit(1);
        }
    } else {
        echo "No wire:initial-data found\n";
        exit(1);
    }
} catch (Throwable $e) {
    echo "Exception: " . get_class($e) . " - " . $e->getMessage() . "\n";
}
