const CACHE_NAME = 'ratas-queiles-v1';

// Durante la instalación, no cacheamos nada complejo para no romper las sesiones PHP
self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(clients.claim());
});

// Interceptar las peticiones
self.addEventListener('fetch', (event) => {
    // Si la petición no es HTTP/HTTPS, ignorarla (como extensiones de chrome, etc)
    if (!(event.request.url.indexOf('http') === 0)) return;

    event.respondWith(
        fetch(event.request)
            .then((networkResponse) => {
                return networkResponse;
            })
            .catch(() => {
                // Si falla la red (offline), podríamos devolver una página HTML de "Sin Conexión"
                // pero por ahora solo dejamos que falle nativamente o devuelva caché si existiera
                return caches.match(event.request);
            })
    );
});