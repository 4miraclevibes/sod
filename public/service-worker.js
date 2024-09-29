const CACHE_NAME = 'sod-cache-v1';
const urlsToCache = [
  '/',
  '/css/app.css',
  '/js/app.js',
  '/assets/landing/images/LogoSod.png'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(urlsToCache))
  );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => response || fetch(event.request))
  );
});