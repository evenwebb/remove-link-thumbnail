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
    <style>
        body {
            font-family: Arial, sans-serif;
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
            width: 320px;
        }
        input[type=url], #generatedLink {
            padding: 10px;
            width: 100%;
            box-sizing: border-box;
        }
        button {
            padding: 10px;
            width: 100%;
            margin-top: 10px;
            cursor: pointer;
        }
        #result {
            margin-top: 20px;
            opacity: 0;
            max-height: 0;
            overflow: hidden;
            transition: opacity .5s, max-height .5s;
        }
        #result.show {
            opacity: 1;
            max-height: 100px;
        }
    </style>
</head>
<body>
<?php if (!$target): ?>
    <div class="container">
        <h1>Remove Link Thumbnail</h1>
        <form id="genForm">
            <input id="urlInput" type="url" placeholder="https://example.com" required>
            <button type="submit">Generate</button>
        </form>
        <div id="result">
            <input id="generatedLink" readonly>
            <button id="copyBtn" type="button">Copy</button>
        </div>
        <p style="margin-top:15px;">Or append <code>?u=&lt;your URL&gt;</code> to this page's address.</p>
    </div>
    <script>
    document.getElementById('genForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var url = document.getElementById('urlInput').value.trim();
        if (!url) return;
        if (!/^https?:\/\//i.test(url)) {
            url = 'http://' + url;
        }
        var base = window.location.origin + window.location.pathname;
        var cloaked = base + '?u=' + encodeURIComponent(url);
        document.getElementById('generatedLink').value = cloaked;
        document.getElementById('result').classList.add('show');
    });

    document.getElementById('copyBtn').addEventListener('click', function() {
        var field = document.getElementById('generatedLink');
        field.select();
        field.setSelectionRange(0, 99999);
        try {
            document.execCommand('copy');
        } catch (err) {}
    });
    </script>
    <?php else: ?>
        <p>Redirecting to <a href="<?php echo htmlspecialchars($target, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($target, ENT_QUOTES, 'UTF-8'); ?></a>...</p>
        <noscript>If you are not redirected automatically, click the link above.</noscript>
    <?php endif; ?>
</body>
</html>
