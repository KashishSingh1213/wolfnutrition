<?php
// config/env.php — Load .env file into getenv()

function load_env($path = null) {
    if ($path === null) {
        $path = dirname(__DIR__) . '/.env';
    }

    if (!file_exists($path)) {
        return false;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) continue;

        // Parse KEY=VALUE
        if (strpos($line, '=') === false) continue;

        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        // Remove surrounding quotes
        if (preg_match('/^["\'](.*)["\']$/', $value, $m)) {
            $value = $m[1];
        }

        // Don't override existing env vars
        if (getenv($key) === false) {
            putenv("$key=$value");
        }

        $_ENV[$key] = $value;
    }

    return true;
}

// Auto-load on include
load_env();
