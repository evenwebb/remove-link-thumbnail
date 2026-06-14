<?php
// --- Configuration ---
// Set this to your deployment URL to avoid relying on the Host header.
// Leave empty to auto-detect (falls back to SERVER_NAME if HTTP_HOST is untrusted).
define('SITE_BASE_URL', '');  // e.g. 'https://example.com'

define('MAX_URL_LENGTH', 2048);
define('CACHE_MAX_AGE', 31536000);  // 1 year for blank.png

// --- Input ---
$raw_input = '';
if (!empty($_GET['u'])) {
    $raw_input = trim($_GET['u']);
} elseif (!empty($_GET['url'])) {
    $raw_input = trim($_GET['url']);
}

// Clamp length
if (mb_strlen($raw_input) > MAX_URL_LENGTH) {
    $raw_input = mb_substr($raw_input, 0, MAX_URL_LENGTH);
}

$show_image = isset($_GET['i']) && $_GET['i'] === '0';   // only shown when i=0
$show_title = isset($_GET['t']) && $_GET['t'] === '0';   // only shown when t=0
$target = null;
$domain = null;
$error = null;

if ($raw_input !== '') {
    // Prepend scheme if missing
    if (!preg_match('#^https?://#i', $raw_input)) {
        $raw_input = 'https://' . $raw_input;
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

// --- Helpers ---
if (!function_exists('detect_scheme')) {
function detect_scheme(): string {
    if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
        return 'https';
    }
    if (!empty($_SERVER['REQUEST_SCHEME']) && strtolower($_SERVER['REQUEST_SCHEME']) === 'https') {
        return 'https';
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
        return 'https';
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on') {
        return 'https';
    }
    return 'http';
}
}

if (!function_exists('site_base_url')) {
function site_base_url(): string {
    if (defined('SITE_BASE_URL') && SITE_BASE_URL !== '') {
        return rtrim(SITE_BASE_URL, '/');
    }
    $scheme = detect_scheme();
    $host = !empty($_SERVER['HTTP_X_FORWARDED_HOST'])
        ? trim(explode(',', $_SERVER['HTTP_X_FORWARDED_HOST'])[0])
        : ($_SERVER['SERVER_NAME'] ?? 'localhost');
    return $scheme . '://' . $host;
}
}

$base_url = site_base_url();
$blank_image = $base_url . '/blank.png';

// --- Security headers ---
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header('Content-Security-Policy: default-src \'self\'; style-src \'unsafe-inline\'; script-src \'unsafe-inline\'; img-src \'self\' data:;');

// Add caching for blank.png requests
if (($_SERVER['REQUEST_URI'] ?? '') === '/blank.png') {
    header('Content-Type: image/png');
    header('Cache-Control: public, max-age=' . CACHE_MAX_AGE . ', immutable');
    readfile(__DIR__ . '/blank.png');
    exit;
}

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
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            text-align: center;
            width: 360px;
            max-width: 90vw;
        }
        .error {
            color: #b00020;
            margin-bottom: 10px;
        }
        input[type=text], #generatedLink {
            padding: 10px;
            width: 100%;
            box-sizing: border-box;
            font-size: 14px;
        }
        button {
            padding: 10px;
            width: 100%;
            margin-top: 10px;
            cursor: pointer;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button[type=submit] {
            background: #0066cc;
            color: #fff;
            border-color: #0055aa;
        }
        button[type=submit]:hover {
            background: #0055aa;
        }
        #optionsToggle {
            margin-top: 15px;
            background: #eee;
            border: none;
            padding: 10px;
            cursor: pointer;
            width: 100%;
        }
        #options {
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            transition: max-height .3s, opacity .3s;
        }
        #options.show {
            max-height: 200px;
            opacity: 1;
        }
        .option-item {
            display: flex;
            align-items: center;
            margin-top: 10px;
            justify-content: flex-start;
            font-size: 14px;
        }
        .option-item input[type=checkbox] {
            transform: scale(1.3);
            margin-right: 8px;
        }
        #result {
            margin-top: 20px;
            opacity: 0;
            max-height: 0;
            overflow: hidden;
            transition: opacity .3s, max-height .3s;
        }
        #result.show {
            opacity: 1;
            max-height: 120px;
        }
    </style>
</head>
<body>
<?php if (!$target): ?>
    <div class="container">
        <h1>Remove Link Thumbnail</h1>
        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <form id="genForm" method="get" action="<?= htmlspecialchars($_SERVER['SCRIPT_NAME'] ?? '/index.php', ENT_QUOTES, 'UTF-8') ?>">
            <input id="urlInput" type="text" name="u" inputmode="url" placeholder="example.com" required maxlength="<?= MAX_URL_LENGTH ?>" value="<?= htmlspecialchars($raw_input, ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit">Generate</button>
            <button id="optionsToggle" type="button">Options</button>
            <div id="options">
                <label class="option-item"><input type="checkbox" id="optTitle"<?= $show_title ? ' checked' : '' ?>> Show title in preview</label>
                <label class="option-item"><input type="checkbox" id="optImage"<?= $show_image ? ' checked' : '' ?>> Show image in preview</label>
            </div>
        </form>
        <noscript>
            <p style="margin-top:10px;font-size:13px;color:#666">Or append <code>?u=&lt;your URL&gt;</code> to this page's address.</p>
        </noscript>
        <div id="result">
            <input id="generatedLink" readonly>
            <button id="copyBtn" type="button">Copy</button>
        </div>
    </div>
    <script>
    document.getElementById('optionsToggle').addEventListener('click', function() {
        document.getElementById('options').classList.toggle('show');
    });

    document.getElementById('genForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var url = document.getElementById('urlInput').value.trim();
        if (!url) return;
        var scheme = 'https';
        if (/^https?:\/\//i.test(url)) {
            scheme = url.toLowerCase().startsWith('http://') ? 'http' : 'https';
            url = url.replace(/^https?:\/\//i, '');
        }
        var base = window.location.origin + window.location.pathname;
        var params = [];
        if (scheme === 'http') {
            params.push('scheme=http');
        }
        if (document.getElementById('optImage').checked) {
            params.push('i=0');
        }
        if (document.getElementById('optTitle').checked) {
            params.push('t=0');
        }
        params.push('u=' + encodeURIComponent(url));
        var cloaked = base + '?' + params.join('&');
        document.getElementById('generatedLink').value = cloaked;
        document.getElementById('result').classList.add('show');
    });

    document.getElementById('copyBtn').addEventListener('click', function() {
        var field = document.getElementById('generatedLink');
        field.select();
        field.setSelectionRange(0, 99999);
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(field.value).catch(function() {
                document.execCommand('copy');
            });
        } else {
            document.execCommand('copy');
        }
    });
    </script>
<?php else: ?>
    <p>Redirecting to <a href="<?= htmlspecialchars($target, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($target, ENT_QUOTES, 'UTF-8') ?></a>&#8230;</p>
    <noscript>If you are not redirected automatically, click the link above.</noscript>
    <script>
    window.location.replace(<?= json_encode($target) ?>);
    </script>
<?php endif; ?>
</body>
</html>
