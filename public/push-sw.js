// Service Worker für Push-Nachrichten der Klamottenbörse (Erinnerungen, Rundnachrichten).
self.addEventListener('push', (event) => {
    let daten = { titel: 'Klamottenbörse', text: '', url: '/' };
    try {
        daten = { ...daten, ...event.data.json() };
    } catch {
        daten.text = event.data ? event.data.text() : '';
    }

    event.waitUntil(self.registration.showNotification(daten.titel, {
        body: daten.text,
        icon: '/images/icon-192.png',
        badge: '/images/icon-192.png',
        data: { url: daten.url },
        lang: 'de',
    }));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = event.notification.data?.url || '/';
    event.waitUntil(self.clients.openWindow(url));
});
