# Remove Link Thumbnail

This repository provides a simple PHP script that lets you create links with a blank preview. When the generated link is shared on social media or in chat apps that typically show link previews, it will display a transparent thumbnail and the domain name of the original link only. Clicking the previewless link immediately redirects the user to the intended destination.

## How It Works

The script accepts a URL via the query parameter `u` or `url`. It sets minimal Open Graph and Twitter Card metadata using a 1×1 pixel transparent image. Most crawlers will then produce a blank preview. A quick meta refresh and JavaScript redirect take visitors to the provided URL.

## Usage

1. Upload `index.php` and `blank.png` to any PHP-enabled web host.
2. Access the script by appending your target link to the `u` parameter:
   ```
   https://yourdomain.example/?u=https://example.com/page
   ```
3. Share this generated link. The preview will be blank but clicking it will forward to the original page.

You can also open the site without parameters to use a basic form that generates the link for you.

## Files

- `index.php` – The main PHP script.
- `blank.png` – 1x1 pixel transparent image used for the preview.
- `LICENSE` – MIT license information.

## License

This project is released under the [MIT License](LICENSE).
