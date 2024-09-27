const cacheName = "cleanWash-v12";
const appShellFiles = [
    "/",
    "/index.php",
    "/sw/sw.js",
    "/sw/offline.css"
]

self.addEventListener('install', (event) => {
    console.log("[Service Worker] Installed");

    event.waitUntil(
        // Open the cache
        /*
        caches.open(cacheName).then(function (cache) {
            return cache.addAll(appShellFiles);
        })
        */

        (async () => {
            const cache = await caches.open(cacheName);
            console.log("[Service Worker] Caching App Shell");
            await cache.addAll(appShellFiles);
        })(),
    );
})

self.addEventListener('activate', (event) => {

    var cacheWhitelist = ["cleanWash-v12"];

    event.waitUntil(
        caches.keys().then((keyList) => {
            return Promise.all(
                keyList.map((key) => {
                    if (cacheWhitelist.indexOf(key) === -1) {
                        return caches.delete(key);
                    }
                })
            );
        })
    );
});

self.addEventListener('fetch', (event) => {
    event.respondWith(
        /*
        caches.match(event.request).then(cacheResponse => {
            return cacheResponse || fetch(event.request);
        })
        */
        
        (async () => {
            const r = await caches.match(event.request);
            console.log(`[Service Worker] Fetching resource: ${event.request.url}`);
            if (r) {
                return r;
            }

            const response = await fetch(event.request);
            const cache = await caches.open(cacheName);
            console.log(`[Service Worker] Caching new resource: ${event.request.url}`);
            cache.put(event.request, response.clone());
        })(),

    );
})
