(function () {
    'use strict';
    // Never recreate the outer page while already inside its content frame.
    if (window.self !== window.top || window.__nanaShellChecked) return;
    window.__nanaShellChecked = true;
    var script = document.currentScript;
    if (!script || !script.dataset.nanaShellUrl || !window.fetch || !window.URL) return;
    var root;
    try { root = new URL(script.dataset.nanaShellUrl, window.location.href); } catch (error) { return; }
    if (root.origin !== window.location.origin || root.pathname === window.location.pathname) return;
    var dirty = false;
    document.addEventListener('input', function () { dirty = true; }, { once: true });
    var probe = new URL(root.href);
    probe.searchParams.set('nana_shell_probe', '1');
    // Legacy index.php files ignore this probe. Only the optional integration
    // endpoint explicitly confirms support, so a skin-only install still works.
    fetch(probe.href, { credentials: 'same-origin', cache: 'no-store', headers: { Accept: 'application/json' } })
        .then(function (response) {
            if (!response.ok || (response.headers.get('Content-Type') || '').indexOf('application/json') < 0) return null;
            return response.json();
        })
        .then(function (data) {
            if (!data || data.nana_shell !== 1 || dirty) return;
            root.searchParams.set('nana_route', window.location.pathname + window.location.search + window.location.hash);
            window.location.replace(root.href);
        })
        .catch(function () { /* An unavailable shell must not block the board. */ });
})();
