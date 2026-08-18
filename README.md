# MiniURL - PHP + MySQL URL Shortener

Aplikasi URL shortener sederhana dengan PHP native dan MySQL.

## Fitur

- Membuat short URL otomatis.
- Custom short code opsional.
- Redirect ke URL asli.
- Penghitung jumlah klik.
- Menampilkan 20 link terbaru.
- Prepared statements dengan MySQLi.
- Tampilan responsif.
- Pretty URL menggunakan `.htaccess`.

## Kebutuhan

- PHP 8.0+
- MySQL / MariaDB
- Apache dengan `mod_rewrite` aktif
- XAMPP, Laragon, MAMP, atau hosting PHP biasa

## Instalasi

1. Copy folder `php-url-shortener` ke web root:
   - XAMPP: `htdocs/php-url-shortener`
   - Laragon: `www/php-url-shortener`

2. Buka phpMyAdmin lalu import file:

   `database.sql`

3. Sesuaikan konfigurasi database pada `config.php`:

   ```php
   $host = 'localhost';
   $db   = 'url_shortener';
   $user = 'root';
   $pass = '';
   ```

4. Jalankan:

   `http://localhost/php-url-shortener`

## Jika Pretty URL Tidak Jalan

Pastikan Apache `mod_rewrite` aktif dan `AllowOverride All`.

Sebagai fallback, short URL tetap bisa dipanggil dengan:

`http://localhost/php-url-shortener/index.php?c=abc123`

## Struktur

- `index.php` - halaman utama + proses create + redirect.
- `config.php` - koneksi MySQL.
- `functions.php` - helper.
- `style.css` - tampilan.
- `.htaccess` - routing short URL.
- `database.sql` - struktur database.
