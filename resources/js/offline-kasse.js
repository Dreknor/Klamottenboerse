(function () {
    const DB_NAME = 'klamottenboerse-kasse';
    const STORE_NAME = 'pending_sales';
    const DEVICE_ID = `${Date.now()}-${Math.random().toString(16).slice(2)}`;

    function openDatabase() {
        return new Promise((resolve, reject) => {
            if (!('indexedDB' in window)) {
                reject(new Error('IndexedDB is not supported in this browser.'));
                return;
            }

            const request = window.indexedDB.open(DB_NAME, 1);

            request.onupgradeneeded = function (event) {
                const db = event.target.result;
                if (!db.objectStoreNames.contains(STORE_NAME)) {
                    db.createObjectStore(STORE_NAME, { keyPath: 'id', autoIncrement: true });
                }
            };

            request.onsuccess = function () {
                resolve(request.result);
            };

            request.onerror = function () {
                reject(request.error || new Error('Unable to open IndexedDB.'));
            };
        });
    }

    function addQueuedSale(salePayload) {
        return openDatabase().then((db) => {
            return new Promise((resolve, reject) => {
                const tx = db.transaction(STORE_NAME, 'readwrite');
                const store = tx.objectStore(STORE_NAME);
                const request = store.add({
                    ...salePayload,
                    device_id: DEVICE_ID,
                    created_at: new Date().toISOString(),
                    synced: false,
                });

                request.onsuccess = function () {
                    resolve(request.result);
                };

                request.onerror = function () {
                    reject(request.error || new Error('Unable to write queued sale.'));
                };
            });
        });
    }

    function getQueuedSales() {
        return openDatabase().then((db) => {
            return new Promise((resolve, reject) => {
                const tx = db.transaction(STORE_NAME, 'readonly');
                const store = tx.objectStore(STORE_NAME);
                const request = store.getAll();

                request.onsuccess = function () {
                    resolve(request.result || []);
                };

                request.onerror = function () {
                    reject(request.error || new Error('Unable to read queued sales.'));
                };
            });
        });
    }

    function removeQueuedSales(ids) {
        return openDatabase().then((db) => {
            return Promise.all(ids.map((id) => {
                return new Promise((resolve, reject) => {
                    const tx = db.transaction(STORE_NAME, 'readwrite');
                    const store = tx.objectStore(STORE_NAME);
                    const request = store.delete(id);

                    request.onsuccess = function () {
                        resolve();
                    };

                    request.onerror = function () {
                        reject(request.error || new Error('Unable to remove queued sales.'));
                    };
                });
            }));
        });
    }

    function syncQueuedSales() {
        const csrfToken = document.head.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
            return Promise.resolve(false);
        }

        return getQueuedSales().then((sales) => {
            if (!sales.length) {
                return true;
            }

            const payload = sales.map(({ id, device_id, created_at, synced, ...sale }) => ({
                ...sale,
                device_id,
                created_at,
            }));

            return fetch('/kasse/sync', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Device-Id': DEVICE_ID,
                },
                credentials: 'same-origin',
                body: JSON.stringify({ sales: payload }),
            }).then((response) => {
                if (!response.ok) {
                    throw new Error('Offline sync failed');
                }

                return response.json().then((result) => {
                    if (result.ok) {
                        const ids = sales.map((sale) => sale.id);
                        return removeQueuedSales(ids).then(() => true);
                    }

                    return false;
                });
            });
        });
    }

    function serializeForm(form) {
        const formData = new FormData(form);
        const data = {};

        for (const [key, value] of formData.entries()) {
            if (key === 'submit' || key === '_token') {
                continue;
            }

            data[key] = value;
        }

        return data;
    }

    function setStatus(message, isWarning) {
        const element = document.getElementById('offline-status');
        if (!element) {
            return;
        }

        element.textContent = message;
        element.className = isWarning
            ? 'text-amber-600'
            : 'text-emerald-600';
    }

    function attachFormListener() {
        const form = document.querySelector('form[name="kasse"]');
        if (!form) {
            return;
        }

        form.addEventListener('submit', function (event) {
            if (navigator.onLine) {
                return;
            }

            event.preventDefault();
            const payload = serializeForm(form);

            addQueuedSale(payload)
                .then(() => {
                    setStatus('Offline: Verkauf lokal gespeichert und wird synchronisiert.', true);
                    form.reset();
                })
                .catch(() => {
                    setStatus('Offline: Speicherung fehlgeschlagen, bitte erneut versuchen.', true);
                });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const syncButton = document.getElementById('sync-offline-sales');

        if (navigator.onLine) {
            setStatus('Online: Synchronisation aktiv.', false);
            syncQueuedSales().catch(() => setStatus('Offline-Puffer konnte nicht synchronisiert werden.', true));
        } else {
            setStatus('Offline: Verkäufe werden lokal gepuffert.', true);
        }

        if (syncButton) {
            syncButton.addEventListener('click', function () {
                syncQueuedSales()
                    .then((ok) => setStatus(ok ? 'Synchronisation abgeschlossen.' : 'Keine lokalen Verkäufe zum Synchronisieren.', false))
                    .catch(() => setStatus('Synchronisation fehlgeschlagen.', true));
            });
        }

        attachFormListener();
    });
})();
