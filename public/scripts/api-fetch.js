/* Workforce Management API request integration.
 * Keeps same-origin Laravel API fetches on the session-authenticated Sanctum flow.
 * Existing request bodies and UI behavior are preserved.
 */
(function () {
    'use strict';

    if (window.__wfmApiFetchInstalled || typeof window.fetch !== 'function') {
        return;
    }
    window.__wfmApiFetchInstalled = true;

    const originalFetch = window.fetch.bind(window);
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? String(csrfMeta.content || '') : '';

    window.fetch = function (input, init) {
        let url;
        try {
            const requestUrl = (typeof Request !== 'undefined' && input instanceof Request)
                ? input.url
                : String(input);
            url = new URL(requestUrl, window.location.href);
        } catch (_) {
            return originalFetch(input, init);
        }

        if (url.origin !== window.location.origin) {
            return originalFetch(input, init);
        }

        // Pages such as /admin/employee-edit and /employee/profile make
        // relative fetch('api/...') calls. Normalize those to Laravel's
        // root /api routes instead of letting the browser request /admin/api.
        const nestedApiPath = url.pathname.match(/^\/(?:admin|employee|manager|settings)\/api\/(.+)$/);
        let requestInput = input;
        if (nestedApiPath) {
            url.pathname = '/api/' + nestedApiPath[1];
            if (typeof input === 'string') {
                requestInput = url.href;
            } else if (typeof Request !== 'undefined' && input instanceof Request) {
                requestInput = new Request(url.href, input);
            }
        }

        if (!url.pathname.startsWith('/api/')) {
            return originalFetch(input, init);
        }

        const supplied = init || {};
        const requestMethod = String(
            supplied.method || ((typeof Request !== 'undefined' && input instanceof Request) ? input.method : 'GET')
        ).toUpperCase();
        const headers = new Headers(
            (typeof Request !== 'undefined' && input instanceof Request) ? input.headers : undefined
        );
        new Headers(supplied.headers || {}).forEach(function (value, key) {
            headers.set(key, value);
        });

        if (!headers.has('Accept')) {
            headers.set('Accept', 'application/json');
        }
        if (!headers.has('X-Requested-With')) {
            headers.set('X-Requested-With', 'XMLHttpRequest');
        }

        if (['POST', 'PUT', 'PATCH', 'DELETE'].includes(requestMethod) && csrfToken) {
            headers.set('X-CSRF-TOKEN', csrfToken);
        }

        const options = Object.assign({}, supplied, {
            headers: headers,
            credentials: supplied.credentials || ((typeof Request !== 'undefined' && input instanceof Request) ? input.credentials : 'same-origin')
        });

        return originalFetch(requestInput, options);
    };
})();
