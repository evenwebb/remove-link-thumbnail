# 🔗 Remove Link Thumbnail

A PHP script that creates links with blank previews. When shared on social media, messaging apps, or anywhere that shows link previews, the generated link displays a transparent thumbnail — revealing only the domain. Clicking the link immediately redirects to the intended destination. No tracking, no ads, no JavaScript required for the redirect.

## ✨ Features

- **Blank link previews** — 1×1 transparent RGBA PNG stops Open Graph / Twitter Card scrapers from pulling images or descriptions from the target site
- **Instant redirect** — JavaScript navigates immediately; a `<noscript>` meta-refresh fallback handles non-JS environments
- **Clean URL support** — works with clean URLs like `/example.com` via `.htaccess` rewriting (no `?u=` needed)
- **Security-hardened** — XSS prevention via `json_encode()`, hostname validation, `X-Forwarded-Host` support, content security policy headers
- **External CSS & JS** — styles and scripts extracted to `style.css` and `script.js` for browser caching
- **Interactive form** — a built-in generator form with options to toggle title and image visibility

## 🚀 Quick Start

1. Upload `index.php`, `blank.png`, `style.css`, and `script.js` to any PHP-enabled web host.
2. (Optional) Copy `.htaccess.example` to `.htaccess` for clean URLs.
3. Access the script with a target URL:
   ```
   https://yourdomain.example/?u=example.com/page
   https://yourdomain.example/?s=0&u=example.com/page              # HTTP link
   https://yourdomain.example/?t=0&i=0&u=example.com/page          # show title and preview
   ```
4. Share the generated link. The preview is blank; clicking redirects to the original page.

With clean URLs enabled:
```
https://yourdomain.example/example.com/page
```

## ⚙️ Configuration

Edit `index.php` to customize:

| Setting | Default | Description |
|---------|---------|-------------|
| `SITE_BASE_URL` | auto-detected | Base URL of your installation (set if auto-detection fails behind proxies) |
| CSP headers | enabled | Content-Security-Policy, X-Frame-Options, X-Content-Type-Options, Referrer-Policy |

## 📁 Files

| File | Purpose |
|------|---------|
| `index.php` | Main PHP script with form, redirect, and metadata generation |
| `blank.png` | Programmatically-generated 68-byte 1×1 RGBA transparent PNG |
| `style.css` | External stylesheet with CSS custom properties for theming |
| `script.js` | External JavaScript for clipboard copy and redirect logic |
| `.htaccess.example` | Clean URL rewriting + static asset caching + security headers |

## 🔒 Security

- **XSS prevention**: URL output in JavaScript context uses `json_encode()` instead of `addslashes()`
- **Host header injection**: Uses `SERVER_NAME` with `X-Forwarded-Host` support instead of `HTTP_HOST`
- **URL validation**: `parse_url()` with hostname presence check instead of `filter_var(FILTER_VALIDATE_URL)`
- **Content Security Policy**: Restrictive CSP headers block inline scripts and restrict resource sources
- **Input length limits**: Query parameters have maximum lengths enforced
- **Operator precedence fix**: `$_SERVER['REQUEST_URI'] ?? '' === '/blank.png'` correctly parenthesized

## 📄 License

This project is released under the [MIT License](LICENSE).
