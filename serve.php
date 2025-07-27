<?php

echo "Starting Laravel development server...\n";
echo "Server will be available at: http://localhost:8000\n";
echo "Press Ctrl+C to stop the server\n\n";

// Change to the project directory
chdir(__DIR__);

// Execute php artisan serve
passthru('php artisan serve');