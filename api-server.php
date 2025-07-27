<?php

echo "Starting Laravel API-only server...\n";
echo "API endpoints available at: http://localhost:8000/api\n";
echo "Web routes disabled\n";
echo "Press Ctrl+C to stop\n\n";

chdir(__DIR__);

// Modify bootstrap to skip web routes
$bootstrap = <<<'PHP'
<?php
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

// Skip web routes registration
$app->booted(function ($app) {
    $app['router']->getRoutes()->refreshNameLookups();
});

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
PHP;

file_put_contents('public/api-index.php', $bootstrap);

passthru('php -S localhost:8000 -t public public/api-index.php');