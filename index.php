<?php
// --- Configuration ---
defined('RLT_SITE_BASE_URL')  or define('RLT_SITE_BASE_URL', '');   // e.g. 'https://example.com'
defined('RLT_MAX_URL_LENGTH') or define('RLT_MAX_URL_LENGTH', 2048);
defined('RLT_CACHE_MAX_AGE')  or define('RLT_CACHE_MAX_AGE', 31536000);

// --- Input ---
$raw_input = '';
if (!empty($_GET['u'])) {
    $raw_input = trim($_GET['u']);
} elseif (!empty($_GET['url'])) {
    $raw_input = trim($_GET['url']);
}
if (mb_strlen($raw_input) > RLT_MAX_URL_LENGTH) {
    $raw_input = mb_substr($raw_input, 0, RLT_MAX_URL_LENGTH);
}

$force_scheme = (isset($_GET['s']) && $_GET['s'] === '0') ? 'http' : null;
$show_image   = isset($_GET['i']) && $_GET['i'] === '0';
$show_title   = isset($_GET['t']) && $_GET['t'] === '0';
$target = null;
$domain = null;
$error  = null;

if ($raw_input !== '') {
    // Prepend scheme if missing
    $scheme = $force_scheme ?: 'https';
    if (!preg_match('#^https?://#i', $raw_input)) {
        $raw_input = $scheme . '://' . $raw_input;
    }
    $parts = parse_url($raw_input);
    $host = $parts['host'] ?? '';
    if ($host && !preg_match('/[^\x21-\x7e]/', $host)) {
        $target = $raw_input;
        $domain = preg_replace('/^www\./i', '', $host);
    } else {
        $error = 'Please enter a valid URL.';
    }
}

// --- Serve blank.png with caching ---
if (($_SERVER['REQUEST_URI'] ?? '') === '/blank.png') {
    header('Content-Type: image/png');
    header('Cache-Control: public, max-age=' . RLT_CACHE_MAX_AGE . ', immutable');
    readfile(__DIR__ . '/blank.png');
    return;  // safe in included context
}

// --- Security headers ---
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: no-referrer');
    header('Content-Security-Policy: default-src \'self\'; style-src \'self\' \'unsafe-inline\'; script-src \'self\' \'unsafe-inline\'; img-src \'self\' data:;');
}

// --- Helpers ---
function rlt_detect_scheme(): string {
    if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') return 'https';
    if (!empty($_SERVER['REQUEST_SCHEME']) && strtolower($_SERVER['REQUEST_SCHEME']) === 'https') return 'https';
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') return 'https';
    if (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on') return 'https';
    return 'http';
}

function rlt_base_url(): string {
    if (defined('RLT_SITE_BASE_URL') && RLT_SITE_BASE_URL !== '') {
        return rtrim(RLT_SITE_BASE_URL, '/');
    }
    $s = rlt_detect_scheme();
    $h = !empty($_SERVER['HTTP_X_FORWARDED_HOST'])
        ? trim(explode(',', $_SERVER['HTTP_X_FORWARDED_HOST'])[0])
        : ($_SERVER['SERVER_NAME'] ?? 'localhost');
    return $s . '://' . $h;
}

$base_url    = rlt_base_url();
$blank_image = $base_url . '/blank.png';
$style_url   = $base_url . '/style.css';
$script_url  = $base_url . '/script.js';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<?php if ($target && $domain): ?>
    <meta property="og:title" content="<?= htmlspecialchars($domain, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:title" content="<?= htmlspecialchars($domain, ENT_QUOTES, 'UTF-8') ?>">
    <?php if ($show_title): ?>
        <title><?= htmlspecialchars($domain, ENT_QUOTES, 'UTF-8') ?></title>
    <?php else: ?>
        <title>&#8203;</title>
    <?php endif; ?>
    <meta property="og:description" content="Click to visit <?= htmlspecialchars($domain, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:image" content="<?= htmlspecialchars($blank_image, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:image:width" content="1">
    <meta property="og:image:height" content="1">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="<?= htmlspecialchars($blank_image, ENT_QUOTES, 'UTF-8') ?>">
    <noscript>
        <meta http-equiv="refresh" content="0;url=<?= htmlspecialchars($target, ENT_QUOTES, 'UTF-8') ?>">
    </noscript>
<?php else: ?>
    <title>Remove Link Thumbnail</title>
<?php endif; ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($style_url, ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
<?php if (!$target): ?>
    <div class="container">
        <h1>Remove Link Thumbnail</h1>
        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <form id="genForm" method="get" action="<?= htmlspecialchars($_SERVER['SCRIPT_NAME'] ?? '/index.php', ENT_QUOTES, 'UTF-8') ?>">
            <input id="urlInput" type="text" name="u" inputmode="url" placeholder="example.com" required maxlength="<?= RLT_MAX_URL_LENGTH ?>" value="<?= htmlspecialchars($raw_input, ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit">Generate</button>
            <button id="optionsToggle" type="button">Options</button>
            <div id="options">
                <label class="option-item"><input type="checkbox" id="optTitle"<?= $show_title ? ' checked' : '' ?>> Show title in preview</label>
                <label class="option-item"><input type="checkbox" id="optImage"<?= $show_image ? ' checked' : '' ?>> Show image in preview</label>
            </div>
        </form>
        <noscript>
            <p style="margin-top:10px;font-size:13px;color:#666">Or append <code>?u=&lt;your URL&gt;</code> to this page's address. For HTTP links add <code>&amp;s=0</code>.</p>
        </noscript>
        <div id="result">
            <input id="generatedLink" readonly>
            <button id="copyBtn" type="button">Copy</button>
        </div>
    </div>
<?php else: ?>
    <p>Redirecting to <a href="<?= htmlspecialchars($target, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($target, ENT_QUOTES, 'UTF-8') ?></a>&#8230;</p>
    <noscript>If you are not redirected automatically, click the link above.</noscript>
    <script>window.location.replace(<?= json_encode($target) ?>);</script>
<?php endif; ?>
    <script src="<?= htmlspecialchars($script_url, ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
