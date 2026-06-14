(function () {
    'use strict';

    var toggle = document.getElementById('optionsToggle');
    if (toggle) {
        toggle.addEventListener('click', function () {
            document.getElementById('options').classList.toggle('show');
        });
    }

    var form = document.getElementById('genForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var url = document.getElementById('urlInput').value.trim();
            if (!url) return;

            var scheme = 'https';
            if (/^https?:\/\//i.test(url)) {
                scheme = url.toLowerCase().indexOf('http://') === 0 ? 'http' : 'https';
                url = url.replace(/^https?:\/\//i, '');
            }

            var base = window.location.origin + window.location.pathname;
            var params = [];
            if (scheme === 'http') params.push('s=0');
            if (document.getElementById('optImage').checked) params.push('i=0');
            if (document.getElementById('optTitle').checked) params.push('t=0');
            params.push('u=' + encodeURIComponent(url));

            var field = document.getElementById('generatedLink');
            field.value = base + '?' + params.join('&');
            document.getElementById('result').classList.add('show');
        });
    }

    var copyBtn = document.getElementById('copyBtn');
    if (copyBtn) {
        copyBtn.addEventListener('click', function () {
            var field = document.getElementById('generatedLink');
            field.select();
            field.setSelectionRange(0, 99999);
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(field.value).catch(function () {
                    document.execCommand('copy');
                });
            } else {
                document.execCommand('copy');
            }
        });
    }
})();
