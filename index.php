<?php
$target = isset($_GET['u']) ? trim($_GET['u']) : (isset($_GET['url']) ? trim($_GET['url']) : null);

$domain = null;
if ($target) {
    if (!preg_match('#^https?://#i', $target)) {
        $target = 'http://' . $target;
    }
    $parts = parse_url($target);
    $host = $parts['host'] ?? '';
    if ($host) {
        $domain = preg_replace('/^www\./i', '', $host);
    } else {
        $target = null; // invalid
    }
}

function current_scheme() {
    if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] === 'https')) {
        return 'https';
    }
    return 'http';
}
$blank_image = current_scheme() . '://' . $_SERVER['HTTP_HOST'] . '/blank.png';
?>
<!DOCTYPE html>
<html>
<head>
<?php if ($target && $domain): ?>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($domain, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta property="og:title" content="<?php echo htmlspecialchars($domain, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($blank_image, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="twitter:card" content="summary">
    <meta property="twitter:title" content="<?php echo htmlspecialchars($domain, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="twitter:image" content="<?php echo htmlspecialchars($blank_image, ENT_QUOTES, 'UTF-8'); ?>">
    <meta http-equiv="refresh" content="0;url=<?php echo htmlspecialchars($target, ENT_QUOTES, 'UTF-8'); ?>">
    <script>
    window.onload = function() {
        window.location.replace("<?php echo addslashes($target); ?>");
    };
    </script>
<?php else: ?>
    <meta charset="utf-8">
    <title>Remove Link Thumbnail</title>
<?php endif; ?>
    <style>body{font-family:Arial,sans-serif;margin:40px;text-align:center;} input{padding:8px;width:300px;} button{padding:8px 12px;}</style>
</head>
<body>
<?php if (!$target): ?>
    <h1>Remove Link Thumbnail</h1>
    <form method="get" action="">
        <input type="url" name="u" placeholder="https://example.com" required>
        <button type="submit">Generate</button>
    </form>
    <p>Or append <code>?u=&lt;your URL&gt;</code> to this page's address.</p>
<?php else: ?>
    <p>Redirecting to <a href="<?php echo htmlspecialchars($target, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($target, ENT_QUOTES, 'UTF-8'); ?></a>...</p>
    <noscript>If you are not redirected automatically, click the link above.</noscript>
<?php endif; ?>
</body>
</html>
