<?php
require_once __DIR__ . '/functions.php';

// Ambil path dari URL, misal "/link" atau "/history"
$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// Tetap dukung cara lama pakai query string (?c=kode / ?page=history)
$page = $_GET['page'] ?? '';
$code = $_GET['c'] ?? '';

// Kalau belum ketebak dari query string, coba tebak dari path (butuh router.php)
if ($code === '' && $page === '') {
    if ($path === 'history') {
        $page = 'history';
    } elseif ($path !== '' && $path !== 'index.php') {
        $code = $path;
    }
}

// ==== MODE 1: REDIRECT (index.php?c=KODE) ====
if ($code !== '') {
    $row = getUrlByCode($code);

    if (!$row) {
        http_response_code(404);
        echo 'Short URL tidak ditemukan.';
        exit;
    }

    incrementClicks($code);
    header('Location: ' . $row['original_url'], true, 302);
    exit;
}

// ==== MODE 2: HISTORY (index.php?page=history) ====
if ($page === 'history') {
    $urls = getAllUrls();
    ?>
    <h2>History Short URL</h2>
    <p><a href="/">&laquo; Kembali</a></p>

    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>#</th>
            <th>Original URL</th>
            <th>Short URL</th>
            <th>Tipe</th>
            <th>Clicks</th>
            <th>Dibuat</th>
        </tr>
        <?php if (empty($urls)): ?>
            <tr><td colspan="6">Belum ada data.</td></tr>
        <?php endif; ?>
        <?php foreach ($urls as $i => $row): ?>
            <?php $shortUrl = rtrim(BASE_URL, '/') . '/' . rawurlencode($row['short_code']); ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><a href="<?= htmlspecialchars($row['original_url']) ?>" target="_blank">
                    <?= htmlspecialchars($row['original_url']) ?>
                </a></td>
                <td><a href="<?= htmlspecialchars($shortUrl) ?>" target="_blank">
                    <?= htmlspecialchars($shortUrl) ?>
                </a></td>
                <td><?= $row['is_custom'] ? 'Custom' : 'Random' ?></td>
                <td><?= (int) $row['clicks'] ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php
    exit;
}

// ==== MODE 3: FORM UTAMA (index.php) ====
$result = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $originalUrl = trim($_POST['url'] ?? '');
    $customCode  = trim($_POST['custom_code'] ?? '');

    try {
        $newCode = createShortUrl($originalUrl, $customCode !== '' ? $customCode : null);
        $result = rtrim(BASE_URL, '/') . '/' . rawurlencode($newCode);
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!-- Fokus fungsi, UI seadanya. Semua routing pakai query string, tanpa .htaccess -->
<h2>PHP URL Shortener</h2>

<?php if ($result): ?>
    <p>Short URL berhasil dibuat: <a href="<?= htmlspecialchars($result) ?>"><?= htmlspecialchars($result) ?></a></p>
<?php endif; ?>

<?php if ($error): ?>
    <p style="color:red">Error: <?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="post" action="/">
    <label>URL Asli:</label><br>
    <input type="text" name="url" placeholder="https://contoh.com/artikel-panjang" style="width:400px" required><br><br>

    <label>Custom Code (opsional):</label><br>
    <input type="text" name="custom_code" placeholder="kode-unik-saya"><br><br>

    <button type="submit">Shorten</button>
</form>

<p><a href="/history">Lihat History</a></p>
