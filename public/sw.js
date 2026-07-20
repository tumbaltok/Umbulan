const CACHE_NAME = "meta-pwa-v1";
const OFFLINE_URL = "/offline.html";

const FILES_TO_CACHE = [
    "/",
    OFFLINE_URL
];

// Pre-cache critical resources
self.addEventListener("install", (event) => {
    console.log('[META PWA] Service Worker installing...');
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(FILES_TO_CACHE))
    );
});

// Remove old caches
self.addEventListener("activate", (event) => {
    console.log('[META PWA] Service Worker activated.');
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys.map(key => {
                    if (key !== CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            )
        )
    );
    self.clients.claim();
});

// Listen for skip waiting message
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

// Fetch strategy
self.addEventListener("fetch", (event) => {
    const request = event.request;

    // 1. Abaikan method non-GET (POST, PUT, DELETE)
    if (request.method !== 'GET') {
        return;
    }

    // 2. Handling Navigasi Halaman (Blade views)
    if (request.mode === "navigate") {
        event.respondWith(
            fetch(request)
                .then(response => {
                    if (!response || response.status !== 200 || response.type !== 'basic') {
                        return response;
                    }
                    const responseToCache = response.clone();
                    caches.open(CACHE_NAME).then(cache => {
                        cache.put(request, responseToCache);
                    });
                    return response;
                })
                .catch(() => caches.match(request).then(cached => cached || caches.match(OFFLINE_URL)))
        );
        return;
    }

    // 3. Cache-first untuk aset statis (CSS, JS, Gambar, Font)
    if (
        request.destination === "style" ||
        request.destination === "script" ||
        request.destination === "image" ||
        request.destination === "font"
    ) {
        event.respondWith(
            caches.match(request).then(cached => {
                if (cached) return cached;

                return fetch(request).then(response => {
                    if (!response || response.status !== 200 || response.type !== 'basic') {
                        return response;
                    }
                    const responseToCache = response.clone();
                    caches.open(CACHE_NAME).then(cache => {
                        cache.put(request, responseToCache);
                    });
                    return response;
                });
            })
        );
        return;
    }

    // 4. Default Network-First untuk request lainnya
    event.respondWith(
        fetch(request)
            .then(response => {
                if (!response || response.status !== 200 || response.type !== 'basic') {
                    return response;
                }
                const responseToCache = response.clone();
                caches.open(CACHE_NAME).then(cache => {
                    cache.put(request, responseToCache);
                });
                return response;
            })
            .catch(() => caches.match(request))
    );
});

// Background Sync
self.addEventListener('sync', (event) => {
    if (event.tag === 'laravel-pwa-sync') {
        event.waitUntil(syncRequests());
    }
});

async function syncRequests() {
    try {
        const db = await openDB();
        if (!db.objectStoreNames.contains('offline-requests')) return;

        const tx = db.transaction('offline-requests', 'readonly');
        const store = tx.objectStore('offline-requests');
        const requests = await getAllRequests(store);

        for (const req of requests) {
            try {
                const response = await fetch(req.url, {
                    method: req.method,
                    headers: req.headers,
                    body: req.body
                });

                if (response.ok) {
                    const deleteTx = db.transaction('offline-requests', 'readwrite');
                    deleteTx.objectStore('offline-requests').delete(req.id);
                }
            } catch (err) {
                console.error('[META System] Sync offline gagal pada URL:', req.url, err);
            }
        }
    } catch (err) {
        console.error('[META System] Sync DB error:', err);
    }
}

function openDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('laravel-pwa-sync', 1);
        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains('offline-requests')) {
                db.createObjectStore('offline-requests', { keyPath: 'id', autoIncrement: true });
            }
        };
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

function getAllRequests(store) {
    return new Promise((resolve, reject) => {
        const request = store.getAll();
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}
