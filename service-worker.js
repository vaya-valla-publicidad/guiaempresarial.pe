const CACHE_NAME = "guia-empresarial-v5";

const assetsToCache = [
  "./",
  "index.php",
  "assets/css/style.css",
  "assets/css/registro_usuario.css",
  "assets/img/image.png",
  "assets/img/icon-192.png",
  "assets/img/icon-512.png",
  "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css",
  "https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
];

self.addEventListener("install", event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      return cache.addAll(assetsToCache);
    })
  );
  self.skipWaiting();
});

self.addEventListener("activate", event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys.map(key => {
          if (key !== CACHE_NAME) return caches.delete(key);
        })
      )
    )
  );
  self.clients.claim();
});

self.addEventListener("fetch", event => {
  if (event.request.method !== "GET") return;

  const url = new URL(event.request.url);
  
  const isDynamic = url.pathname.endsWith('.php') || 
                    url.pathname.includes('login_usuario') || 
                    url.pathname.includes('registro_usuario') || 
                    url.pathname.includes('admin') || 
                    url.pathname.includes('editor') ||
                    url.search.length > 0;

  if (isDynamic) {
    event.respondWith(fetch(event.request));
    return;
  }

  event.respondWith(
    caches.match(event.request).then(cachedResponse => {
      if (cachedResponse) return cachedResponse;

      return fetch(event.request).then(response => {
        if (!response || response.status !== 200 || response.type !== 'basic') {
          return response;
        }

        const responseToCache = response.clone();
        caches.open(CACHE_NAME).then(cache => {
          cache.put(event.request, responseToCache);
        });

        return response;
      });
    })
  );
});
