// Service Worker der Kasse: Seite, Skripte und Styles werden im Browser abgelegt,
// damit die Kasse auch ohne Netz (neu) geladen werden kann. Verkäufe selbst liegen
// in localStorage und werden von der Seite übertragen, sobald wieder Netz da ist.

const CACHE = 'kasse-v1';
const SEITE = '/kasse';

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(CACHE).then((cache) => cache.add(SEITE)).catch(() => {}));
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((namen) => Promise.all(namen.filter((n) => n.startsWith('kasse-') && n !== CACHE).map((n) => caches.delete(n))))
            .then(() => self.clients.claim()),
    );
});

const zuerstNetz = async (request) => {
    const cache = await caches.open(CACHE);
    try {
        const antwort = await fetch(request);
        if (antwort.ok) cache.put(request, antwort.clone());
        return antwort;
    } catch (fehler) {
        const gespeichert = await cache.match(request, { ignoreSearch: true });
        if (gespeichert) return gespeichert;
        throw fehler;
    }
};

const zuerstSpeicher = async (request) => {
    const cache = await caches.open(CACHE);
    const gespeichert = await cache.match(request);
    if (gespeichert) return gespeichert;
    const antwort = await fetch(request);
    if (antwort.ok) cache.put(request, antwort.clone());
    return antwort;
};

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;

    if (url.pathname === SEITE || url.pathname === SEITE + '/daten') {
        event.respondWith(zuerstNetz(request));
    } else if (url.pathname.startsWith('/build/') || url.pathname.startsWith('/images/')) {
        event.respondWith(zuerstSpeicher(request));
    }
});
