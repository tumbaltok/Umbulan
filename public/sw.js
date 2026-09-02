const CACHE_NAME = "laravel-pwa-1786369035";
const OFFLINE_URL = "/offline.html";

const FILES_TO_CACHE = [
    "/",
    OFFLINE_URL
];

// Pra-simpan aset kritis saat instalasi
self.addEventListener("install", (event) => {
    console.log('[Laravel PWA] Service Worker menginstal...');
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(FILES_TO_CACHE))
    );
});

// Pembersihan cache versi lama
self.addEventListener("activate", (event) => {
    console.log('[Laravel PWA] Service Worker aktif.');
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

// Handler pesan skip waiting
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

// Strategi pengambilan data cache & jaringan
self.addEventListener("fetch", (event) => {

    const request = event.request;

    // Lewati permintaan non-GET agar diproses peramban secara native
    if (request.method !== 'GET') {
        return;
    }

    // Navigasi halaman dengan fallback halaman offline
    if (request.mode === "navigate") {
        event.respondWith(
            fetch(request)
                .catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    // Prioritas Cache-First untuk aset statis
    if (
        request.destination === "style" ||
        request.destination === "script" ||
        request.destination === "image" ||
        request.destination === "font"
    ) {
        event.respondWith(
            caches.match(request)
                .then(cached => {
                    return cached || fetch(request).then(response => {
                        return caches.open(CACHE_NAME).then(cache => {
                            cache.put(request, response.clone());
                            return response;
                        });
                    });
                })
        );
        return;
    }

    // Default: Network-First dengan fallback ke cache
    event.respondWith(
        fetch(request)
            .then(response => {
                return caches.open(CACHE_NAME).then(cache => {
                    cache.put(request, response.clone());
                    return response;
                });
            })
            .catch(async (error) => {
                return caches.match(request);
            })
    );
});

// Sinkronisasi data di latar belakang
self.addEventListener('sync', (event) => {
    if (event.tag === 'laravel-pwa-sync') {
        event.waitUntil(syncRequests());
    }
});

async function syncRequests() {
    const db = await openDB();
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
            console.error('[Laravel PWA] Sync failed for:', req.url, err);
        }
    }
}

function openDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('laravel-pwa-sync', 1);
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
