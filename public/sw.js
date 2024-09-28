// Version of the service worker
const version = 'v1.0.0';

// Name of the cache
const CACHE_NAME = 'static-' + version;
const DYNAMIC_CACHE_NAME = 'dynamic-' + version;
// Bonjour ! Voici quelques suggestions d'améliorations pour ce service worker :

// 1. Ajouter une stratégie de mise en cache plus robuste
const networkFirst = async (request) => {
  try {
    const networkResponse = await fetch(request);
    const cache = await caches.open(DYNAMIC_CACHE_NAME);
    cache.put(request, networkResponse.clone());
    return networkResponse;
  } catch (error) {
    const cachedResponse = await caches.match(request);
    return cachedResponse || Promise.reject('no-match');
  }
};

// 2. Gérer les erreurs de façon plus élégante
self.addEventListener('fetch', event => {
  event.respondWith(
    networkFirst(event.request).catch(error => {
      console.error('Fetch failed:', error);
      return new Response('Erreur réseau, veuillez réessayer plus tard.', {
        status: 503,
        statusText: 'Service Unavailable'
      });
    })
  );
});

// 3. Ajouter une fonction pour mettre à jour le cache périodiquement
const updateCache = async () => {
  const cache = await caches.open(CACHE_NAME);
  const response = await fetch('sw-cache-list.json');
  const cacheList = await response.json();
  await cache.addAll(cacheList);
};

// Mettre à jour le cache toutes les 24 heures
setInterval(updateCache, 24 * 60 * 60 * 1000);

// 4. Ajouter un gestionnaire de messages pour communiquer avec la page
self.addEventListener('message', event => {
  if (event.data.action === 'skipWaiting') {
    self.skipWaiting();
  }
});

// Ces améliorations rendent le service worker plus robuste et flexible.
// N'oubliez pas d'adapter ces suggestions à vos besoins spécifiques.

// Install the service worker
self.addEventListener('install', event => {
    event.waitUntil(caches.open(CACHE_NAME).then(cache => {
        return fetch('/sw-cache-list.json')
            .then(response => response.json())
            .then(cacheList => {
                return Promise.all(cacheList.map(url => addToCache(cache, url)));
            });
    }));
    self.skipWaiting();
});

// Activate the service worker
self.addEventListener('activate', event => {

    // Delete all caches that aren't CACHE_NAME.
    const cacheWhitelist = [CACHE_NAME, DYNAMIC_CACHE_NAME];
    
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheWhitelist.indexOf(cacheName) === -1) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );

    // Force the waiting service worker to become the active service worker.
    self.clients.claim();
});

// When a request is received, check the cache first.
// If the request is found in the cache, return it.
// If not, fetch the resource from the network.
self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request).then(response => {
            return response || fetch(event.request).then(response => {
                return caches.open(DYNAMIC_CACHE_NAME).then(cache => {
                    cache.put(event.request.url, response.clone());
                    return response;
                });
            });
        })
    );
});

function addToCache(cache, url) {
    return fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Échec de la récupération: ${url}`);
            }
            return cache.put(url, response);
        })
        .catch(error => {
            console.error('Erreur lors de la mise en cache:', url, error);
        });
}