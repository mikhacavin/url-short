<?php
declare(strict_types=1);

function generateCode(int $length = 6): string
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $max = strlen($chars) - 1;
    $result = '';

    for ($i = 0; $i < $length; $i++) {
        $result .= $chars[random_int(0, $max)];
    }

    return $result;
}

function isValidUrl(string $url): bool
{
    return filter_var($url, FILTER_VALIDATE_URL) !== false
        && in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true);
}

function baseUrl(): string
{
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));

    $script = rtrim($script, '/');
    return $scheme . '://' . $host . ($script ? $script : '');
}

function shortUrl(string $code): string
{
    return baseUrl() . '/' . rawurlencode($code);
}
