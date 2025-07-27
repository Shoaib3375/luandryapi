<?php

$host = '127.0.0.1';
$port = '8000';

echo "Starting Laravel API server...\n";
echo "API endpoints available at: http://{$host}:{$port}/api\n";
echo "Example: http://{$host}:{$port}/api/orders\n";
echo "Press Ctrl+C to stop the server\n\n";

chdir(__DIR__);
passthru("php artisan serve --host={$host} --port={$port}");
