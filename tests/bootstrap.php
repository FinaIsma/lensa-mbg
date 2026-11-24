<?php

require __DIR__.'/../vendor/autoload.php';

$_ENV['APP_BASE_PATH'] = dirname(__DIR__);
putenv('APP_BASE_PATH='.dirname(__DIR__));

// Pastikan namespace App, Tests, dan Database dimuat dari worktree lokal
spl_autoload_register(function (string $class): void {
    if (str_starts_with($class, 'App\\')) {
        $file = dirname(__DIR__).'/app/'.str_replace('\\', '/', substr($class, 4)).'.php';
        if (file_exists($file)) {
            require_once $file;
        }
    } elseif (str_starts_with($class, 'Tests\\')) {
        $file = dirname(__DIR__).'/tests/'.str_replace('\\', '/', substr($class, 6)).'.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
}, true, true);
