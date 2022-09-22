const VERSION = "1.0";
const CACHE_VERSION = "glm-offline-v" + VERSION;
const OFFLINE_URL = "/offline";

let cacheablePages = [
    // "/",
    // "/offline",
    // "/css/public.css",
    // "/fonts/font-awesome/fontawesome-webfont.woff2",
    // "/img/header/homepage.jpg",
    // "/img/logo/logo.svg",
    // "/js/public.js"
];

// Pre-Cache all cacheable pags
self.addEventListener("install", event => {
    event.waitUntil(() => {
        return caches.open(CACHE_VERSION).then(cache => {
            return cache.addAll(cacheablePages);
        });
    });
});

// Cleanup old cache storages
self.addEventListener("activate", event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (CACHE_VERSION !== cacheName) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

self.addEventListener("fetch", event => {
    event.respondWith(
        caches.match(event.request)
        .then(response => {
            // Cache hit » return response
            if (response) {
                return response;
            }

            // Clone the request
            var fetchRequest = event.request.clone();

            return fetch(fetchRequest).then(
                response => {
                    // Check response
                    if (!response || response.status !== 200 || response.type !== 'basic') {
                        return response;
                    }

                    // Clone the response
                    var responseToCache = response.clone();

                    caches.open(CACHE_VERSION)
                        .then(cache => {
                            cache.put(event.request, responseToCache);
                        });

                    return response;
                }
            );
        })
        .catch(() => {
            // Return the offline page
            if (caches.match(OFFLINE_URL)) {
                return caches.match(OFFLINE_URL);
            }
        })
    );
});