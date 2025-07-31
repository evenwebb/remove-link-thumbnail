<?php
$raw_input = isset($_GET['u']) ? trim($_GET['u']) : (isset($_GET['url']) ? trim($_GET['url']) : '');
$scheme_param = isset($_GET['s']) ? trim($_GET['s']) : null;
$hide_image = !isset($_GET['i']) || $_GET['i'] !== '0';
$hide_title = !isset($_GET['t']) || $_GET['t'] !== '0';
$target = null;
$domain = null;
$error = null;

if ($raw_input !== '') {
    $scheme = 'https';
    if ($scheme_param === '0') {
        $scheme = 'http';
    }
    if (preg_match('#^(https?)://(.+)#i', $raw_input, $m)) {
        $scheme = strtolower($m[1]);
        $raw_input = $m[2];
    }
    $target = $scheme . '://' . $raw_input;
    if (filter_var($target, FILTER_VALIDATE_URL)) {
        $parts = parse_url($target);
        $host = $parts['host'] ?? '';
        if ($host) {
            $domain = preg_replace('/^www\\./i', '', $host);
        } else {
            $error = 'Please enter a valid URL.';
            $target = null;
        }
    } else {
        $error = 'Please enter a valid URL.';
        $target = null;
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
    <?php if (!$hide_title): ?>
        <title><?php echo htmlspecialchars($domain, ENT_QUOTES, 'UTF-8'); ?></title>
        <meta property="og:title" content="<?php echo htmlspecialchars($domain, ENT_QUOTES, 'UTF-8'); ?>">
        <meta property="twitter:title" content="<?php echo htmlspecialchars($domain, ENT_QUOTES, 'UTF-8'); ?>">
    <?php else: ?>
        <title></title>
    <?php endif; ?>
    <?php if ($hide_image): ?>
        <meta property="og:image" content="<?php echo htmlspecialchars($blank_image, ENT_QUOTES, 'UTF-8'); ?>">
        <meta property="twitter:image" content="<?php echo htmlspecialchars($blank_image, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>
    <meta property="twitter:card" content="summary">
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
        .error {
            color: #b00020;
            margin-bottom: 10px;
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
            transition: max-height .5s, opacity .5s;
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
        }
        .option-item input[type=checkbox] {
            transform: scale(1.4);
            margin-right: 8px;
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
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <form id="genForm">
            <input id="urlInput" type="url" placeholder="https://example.com" required value="<?php echo htmlspecialchars($raw_input, ENT_QUOTES, 'UTF-8'); ?>">
            <button type="submit">Generate</button>
            <button id="optionsToggle" type="button">Options</button>
            <div id="options">
                <label class="option-item"><input type="checkbox" id="optTitle" checked> Hide title</label>
                <label class="option-item"><input type="checkbox" id="optImage" checked> Hide image preview</label>
            </div>
        </form>
        <div id="result">
            <input id="generatedLink" readonly>
            <button id="copyBtn" type="button">Copy</button>
        </div>
        <p style="margin-top:15px;">Or append <code>?u=&lt;your URL&gt;</code> to this page's address. For HTTP links add <code>&amp;s=0</code>.</p>
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
            params.push('s=0');
        }
        if (!document.getElementById('optImage').checked) {
            params.push('i=0');
        }
        if (!document.getElementById('optTitle').checked) {
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
