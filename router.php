<?php
// Jalankan server pakai router ini:
//   php -S 0.0.0.0:8080 router.php
// Semua request lewat sini dulu sebelum masuk ke index.php,
// jadi /link, /history, dsb bisa "ditangkap" tanpa .htaccess/mod_rewrite.

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Kalau yang diminta adalah file statis yang beneran ada di folder ini
// (misal /style.css, /favicon.ico), biarkan PHP built-in server yang serve langsung.
if ($uri !== '/' && file_exists(__DIR__ . $uri) && !is_dir(__DIR__ . $uri)) {
    return false;
}

// Selain itu, semua request (termasuk /link) diarahkan ke index.php
require __DIR__ . '/index.php';
