<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$paths = [
    '/filament/assets/app.css?id=051ae0c568738c610482ba758aff7ae4',
    '/livewire/message/filament.core.auth.login',
];

foreach ($paths as $path) {
    $request = Illuminate\Http\Request::create($path, 'GET');
    $response = $kernel->handle($request);
    echo "REQUEST: $path\n";
    echo "STATUS: " . $response->getStatusCode() . "\n";
    echo "HEADERS: " . json_encode($response->headers->all()) . "\n";
    echo "BODY_SNIPPET: " . substr((string) $response->getContent(), 0, 500) . "\n";
    echo "---\n";
    $kernel->terminate($request, $response);
}
