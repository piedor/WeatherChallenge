const CACHE_NAME = 'weatherchallenge-v1';

// Installazione
self.addEventListener('install', (event) => {
    self.skipWaiting();
    console.log('Service Worker installato');
});

// Attivazione
self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
    console.log('Service Worker attivato');
});

// Fetch (cache first per risorse statiche)
self.addEventListener('fetch', (event) => {
    event.respondWith(
        caches.match(event.request).then((response) => {
            return response || fetch(event.request);
        })
    );
});