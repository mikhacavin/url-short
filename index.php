<?php
declare(strict_types=1);

require __DIR__ . '/config.php';
require __DIR__ . '/functions.php';

$error = '';
$success = '';
$createdShortUrl = '';

/*
|--------------------------------------------------------------------------
| Redirect short URL
|--------------------------------------------------------------------------
| Mendukung:
| - /abc123         (dengan .htaccess)
| - /index.php?c=abc123
*/
$code = trim($_GET['c'] ?? '');

if ($code !== '') {
    $stmt = $conn->prepare('SELECT id, original_url FROM links WHERE short_code = ? LIMIT 1');
    $stmt->bind_param('s', $code);
    $stmt->execute();
    $result = $stmt->get_result();
    $link = $result->fetch_assoc();
    $stmt->close();

    if ($link) {
        $update = $conn->prepare('UPDATE links SET clicks = clicks + 1, last_clicked_at = NOW() WHERE id = ?');
        $update->bind_param('i', $link['id']);
        $update->execute();
        $update->close();

        header('Location: ' . $link['original_url'], true, 302);
        exit;
    }

    http_response_code(404);
    $error = 'Short URL tidak ditemukan.';
}

/*
|--------------------------------------------------------------------------
| Create short URL
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $originalUrl = trim($_POST['original_url'] ?? '');
    $customCode  = trim($_POST['custom_code'] ?? '');

    if (!isValidUrl($originalUrl)) {
        $error = 'Masukkan URL yang valid, contoh: https://example.com/halaman';
    } elseif ($customCode !== '' && !preg_match('/^[a-zA-Z0-9_-]{3,30}$/', $customCode)) {
        $error = 'Custom code hanya boleh huruf, angka, underscore, dan strip (3–30 karakter).';
    } else {
        if ($customCode !== '') {
            $shortCode = $customCode;

            $check = $conn->prepare('SELECT id FROM links WHERE short_code = ? LIMIT 1');
            $check->bind_param('s', $shortCode);
            $check->execute();
            $exists = $check->get_result()->num_rows > 0;
            $check->close();

            if ($exists) {
                $error = 'Custom code tersebut sudah digunakan.';
            }
        } else {
            do {
                $shortCode = generateCode(6);

                $check = $conn->prepare('SELECT id FROM links WHERE short_code = ? LIMIT 1');
                $check->bind_param('s', $shortCode);
                $check->execute();
                $exists = $check->get_result()->num_rows > 0;
                $check->close();
            } while ($exists);
        }

        if ($error === '') {
            $stmt = $conn->prepare('INSERT INTO links (original_url, short_code) VALUES (?, ?)');
            $stmt->bind_param('ss', $originalUrl, $shortCode);

            if ($stmt->execute()) {
                $createdShortUrl = shortUrl($shortCode);
                $success = 'Short URL berhasil dibuat.';
            } else {
                $error = 'Gagal menyimpan URL.';
            }

            $stmt->close();
        }
    }
}

$links = [];
$result = $conn->query(
    'SELECT original_url, short_code, clicks, created_at, last_clicked_at
     FROM links
     ORDER BY id DESC
     LIMIT 20'
);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $links[] = $row;
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MiniURL - PHP URL Shortener</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="container">
    <section class="hero">
        <div class="badge">PHP + MySQL</div>
        <h1>MiniURL</h1>
        <p>URL shortener sederhana, ringan, dan mudah dipasang.</p>
    </section>

    <section class="card">
        <form method="post" autocomplete="off">
            <label for="original_url">URL tujuan</label>
            <input
                type="url"
                id="original_url"
                name="original_url"
                placeholder="https://contoh.com/halaman-yang-panjang"
                value="<?= htmlspecialchars($_POST['original_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                required
            >

            <label for="custom_code">
                Custom code
                <span class="muted">(opsional)</span>
            </label>
            <div class="custom-row">
                <span><?= htmlspecialchars(baseUrl(), ENT_QUOTES, 'UTF-8') ?>/</span>
                <input
                    type="text"
                    id="custom_code"
                    name="custom_code"
                    maxlength="30"
                    placeholder="misalnya: kemensos"
                    value="<?= htmlspecialchars($_POST['custom_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                >
            </div>

            <button type="submit">Pendekkan URL</button>
        </form>

        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if ($success && $createdShortUrl): ?>
            <div class="result-box">
                <div>
                    <small><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></small>
                    <a id="shortResult" href="<?= htmlspecialchars($createdShortUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank">
                        <?= htmlspecialchars($createdShortUrl, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </div>
                <button type="button" class="copy-btn" onclick="copyShortUrl()">Copy</button>
            </div>
        <?php endif; ?>
    </section>

    <section class="history">
        <div class="section-head">
            <div>
                <h2>20 link terbaru</h2>
                <p>Statistik klik tersimpan otomatis.</p>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Short URL</th>
                    <th>URL Tujuan</th>
                    <th>Klik</th>
                    <th>Dibuat</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!$links): ?>
                    <tr>
                        <td colspan="4" class="empty">Belum ada short URL.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($links as $link): ?>
                        <?php $short = shortUrl($link['short_code']); ?>
                        <tr>
                            <td>
                                <a href="<?= htmlspecialchars($short, ENT_QUOTES, 'UTF-8') ?>" target="_blank">
                                    <?= htmlspecialchars($link['short_code'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </td>
                            <td class="url-cell" title="<?= htmlspecialchars($link['original_url'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($link['original_url'], ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td><strong><?= (int) $link['clicks'] ?></strong></td>
                            <td><?= htmlspecialchars(date('d M Y H:i', strtotime($link['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<script>
function copyShortUrl() {
    const el = document.getElementById('shortResult');
    if (!el) return;

    navigator.clipboard.writeText(el.href).then(() => {
        const btn = document.querySelector('.copy-btn');
        if (!btn) return;
        const oldText = btn.textContent;
        btn.textContent = 'Copied!';
        setTimeout(() => btn.textContent = oldText, 1400);
    });
}
</script>
</body>
</html>
