<?php
// Simple test to check if Laravel routes work
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/api/register', 'GET');
$response = $kernel->handle($request);

echo "Response: " . $response->getContent() . "\n";
echo "Status: " . $response->getStatusCode() . "\n";