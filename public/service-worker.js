self.addEventListener('install', function(event) {
    event.waitUntil(
      caches.open('sod-cache-v1').then(function(cache) {
        return cache.addAll([
          '/',
          '/css/app.css',
          '/js/app.js',
          // Tambahkan asset lain yang perlu di-cache
        ]);
      })
    );
  });
  
  self.addEventListener('fetch', function(event) {
    event.respondWith(
      caches.match(event.request).then(function(response) {
        return response || fetch(event.request);
      })
    );
  });