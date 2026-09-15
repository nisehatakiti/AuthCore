<?php

declare(strict_types=1);

namespace AuthCore;

final class Autoloader
{
    private static string $basePath = '';

    public static function register(string $basePath): void
    {
        self::$basePath = rtrim($basePath, '/\\');
        spl_autoload_register([self::class, 'autoload']);
    }

    private static function autoload(string $class): void
    {
        $prefix = __NAMESPACE__ . '\\';

        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            return;
        }

        $relative = substr($class, strlen($prefix));
        $file = self::$basePath . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';

        if (is_readable($file)) {
            require_once $file;
        }
    }
}
