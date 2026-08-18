<?php
require_once __DIR__ . '/config.php';

// Generate kode acak untuk short url
function generateRandomCode($length = 6) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $code;
}

// Cek apakah short_code sudah dipakai
function codeExists($code) {
    $db = getDB();
    $stmt = $db->prepare('SELECT id FROM urls WHERE short_code = ?');
    $stmt->execute([$code]);
    return (bool) $stmt->fetch();
}

// Validasi format custom code: huruf, angka, dash, underscore, 3-50 karakter
function isValidCustomCode($code) {
    return (bool) preg_match('/^[a-zA-Z0-9_-]{3,50}$/', $code);
}

// Validasi URL
function isValidUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

// Buat short url baru, return array short_code atau throw Exception kalau gagal
function createShortUrl($originalUrl, $customCode = null) {
    if (!isValidUrl($originalUrl)) {
        throw new Exception('URL tidak valid.');
    }

    $db = getDB();

    if ($customCode !== null && $customCode !== '') {
        if (!isValidCustomCode($customCode)) {
            throw new Exception('Custom code hanya boleh huruf, angka, - dan _, panjang 3-50 karakter.');
        }
        if (codeExists($customCode)) {
            throw new Exception('Custom code sudah dipakai, coba yang lain.');
        }
        $code = $customCode;
        $isCustom = 1;
    } else {
        // generate sampai dapat kode yang unik
        do {
            $code = generateRandomCode(6);
        } while (codeExists($code));
        $isCustom = 0;
    }

    $stmt = $db->prepare('INSERT INTO urls (original_url, short_code, is_custom) VALUES (?, ?, ?)');
    $stmt->execute([$originalUrl, $code, $isCustom]);

    return $code;
}

// Ambil data url berdasarkan short_code
function getUrlByCode($code) {
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM urls WHERE short_code = ?');
    $stmt->execute([$code]);
    return $stmt->fetch();
}

// Tambah jumlah klik
function incrementClicks($code) {
    $db = getDB();
    $stmt = $db->prepare('UPDATE urls SET clicks = clicks + 1 WHERE short_code = ?');
    $stmt->execute([$code]);
}

// Ambil semua history, terbaru dulu
function getAllUrls() {
    $db = getDB();
    $stmt = $db->query('SELECT * FROM urls ORDER BY created_at DESC');
    return $stmt->fetchAll();
}
