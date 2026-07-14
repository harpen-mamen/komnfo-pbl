<?php
require __DIR__ . '/../vendor/autoload.php';

/** @var \Illuminate\Routing\RouteCollection $routes */
$routes = app('router')->getRoutes();
$route = $routes->getByName('livewire.message');
if (! $route) {
    echo "No route named livewire.message\n";
    exit(1);
}

echo 'uri: ' . $route->uri() . PHP_EOL;
echo 'methods: ' . implode(',', $route->methods()) . PHP_EOL;
echo 'wheres: ' . json_encode($route->wheres()) . PHP_EOL;
$compiled = $route->getCompiled();
if ($compiled) {
    echo 'regex: ' . $compiled->getRegex() . PHP_EOL;
}
$parameters = ['name' => 'filament.core.auth.login'];
$matcher = $route->bind(app('request')->create('/livewire/message/' . rawurlencode($parameters['name']), 'POST'));
var_dump($matcher ? true : false);
