const CACHE_NAME = 'ratas-queiles-v2';

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(clients.claim());
});

self.addEventListener('fetch', (event) => {
    // Si no es una petición http/https, la ignoramos
    if (!(event.request.url.indexOf('http') === 0)) return;

    // EXCEPCIÓN CLAVE: Si la petición va a Cloudinary o no es un GET, 
    // dejamos que vaya directa a la red sin pasar por la lógica de caché del SW.
    if (event.request.url.includes('cloudinary.com') || event.request.method !== 'GET') {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((networkResponse) => {
                return networkResponse;
            })
            .catch(() => {
                return caches.match(event.request).then((cachedResponse) => {
                    // Si está en caché lo devuelve, si no, devuelve un error 503 en lugar de fallar
                    return cachedResponse || new Response('Sin conexión', { status: 503, statusText: 'Offline' });
                });
            })
    );
});