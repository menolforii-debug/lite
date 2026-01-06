<?php

declare(strict_types=1);

use LiteCMS\Config;

require_once __DIR__ . '/src/Config.php';

spl_autoload_register(function (string $class): void {
    $prefix = 'LiteCMS\\';
    if (str_starts_with($class, $prefix) === false) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = __DIR__ . '/src/' . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($path)) {
        require_once $path;
    }
});

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

Config::init();
