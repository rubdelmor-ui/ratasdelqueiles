const CACHE_NAME = 'ratas-queiles-v1';

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(clients.claim());
});

self.addEventListener('fetch', (event) => {
    if (!(event.request.url.indexOf('http') === 0)) return;

    event.respondWith(
        fetch(event.request)
            .then((networkResponse) => {
                return networkResponse;
            })
            .catch(() => {
                return caches.match(event.request).then((cachedResponse) => {
                    // Si está en caché lo devuelve, si no, devuelve un error 503 en lugar de fallar (TypeError)
                    return cachedResponse || new Response('Sin conexión', { status: 503, statusText: 'Offline' });
                });
            })
    );
});