<?php
// ==== KONFIGURASI DATABASE ====
define('DB_HOST', 'localhost');
define('DB_NAME', 'url_shortener');
define('DB_USER', 'root');
define('DB_PASS', '');

// Base URL aplikasi (tanpa trailing slash di akhir).
// Ini domain Cloudflare Tunnel kamu, misal: https://s.mikhacavin.com
define('BASE_URL', 'https://s.mikhacavin.com');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}
