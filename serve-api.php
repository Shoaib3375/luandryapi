?php

$host = '127.0.0.1';
$port = '8000';

echo "Starting Laravel API server (API routes only)...\n";
echo "API will be available at: http://{$host}:{$port}/api\n";
echo "Press Ctrl+C to stop the server\n\n";

chdir(__DIR__);

// Set environment variable to disable web routes
putenv('LARAVEL_SKIP_WEB_ROUTES=true');

passthru("php -S {$host}:{$port} -t public public/index.php");