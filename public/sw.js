const CACHE_NAME = 'ratas-queiles-v2';

self.addEventListener('install', () => {
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(clients.claim());
});

self.addEventListener('fetch', (event) => {
  if (!(event.request.url.indexOf('http') === 0)) return;

  if (event.request.url.includes('cloudinary.com') || event.request.method !== 'GET') {
    return;
  }

  event.respondWith(
    fetch(event.request)
      .then((networkResponse) => networkResponse)
      .catch(() =>
        caches.match(event.request).then((cachedResponse) => {
          return cachedResponse || new Response('Sin conexión', { status: 503, statusText: 'Offline' });
        })
      )
  );
});
