// Push-Nachrichten auf diesem Gerät ein-/ausschalten. Funktioniert in Chrome, Edge, Firefox
// und Safari; auf dem iPhone erst, wenn die Seite zum Home-Bildschirm hinzugefügt wurde.

const base64ZuBytes = (text) => {
    const padding = '='.repeat((4 - (text.length % 4)) % 4);
    const roh = atob((text + padding).replace(/-/g, '+').replace(/_/g, '/'));
    return Uint8Array.from([...roh].map((z) => z.charCodeAt(0)));
};

export function pushSchalter({ schluessel, urls }) {
    return {
        zustand: 'pruefe', // pruefe | nicht_moeglich | ios_installieren | blockiert | aus | an
        laeuft: false,
        meldung: '',

        async init() {
            const istIos = /iPhone|iPad/.test(navigator.userAgent);
            const installiert = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone;
            if (!('serviceWorker' in navigator) || !('PushManager' in window) || !window.isSecureContext) {
                this.zustand = istIos && !installiert ? 'ios_installieren' : 'nicht_moeglich';
                return;
            }
            if (Notification.permission === 'denied') {
                this.zustand = 'blockiert';
                return;
            }
            const registrierung = await navigator.serviceWorker.getRegistration('/');
            const abo = await registrierung?.pushManager.getSubscription();
            this.zustand = abo ? 'an' : 'aus';
        },

        async einschalten() {
            this.laeuft = true;
            this.meldung = '';
            try {
                if ((await Notification.requestPermission()) !== 'granted') {
                    this.zustand = 'blockiert';
                    return;
                }
                const registrierung = await navigator.serviceWorker.register('/push-sw.js', { scope: '/' });
                await navigator.serviceWorker.ready;
                const abo = await registrierung.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: base64ZuBytes(schluessel),
                });
                await this.senden('POST', urls.speichern, abo.toJSON());
                this.zustand = 'an';
                this.meldung = 'Eingeschaltet. Zum Ausprobieren: „Testnachricht senden“.';
            } catch (fehler) {
                this.meldung = 'Das hat nicht geklappt: ' + fehler.message;
            } finally {
                this.laeuft = false;
            }
        },

        async ausschalten() {
            this.laeuft = true;
            try {
                const registrierung = await navigator.serviceWorker.getRegistration('/');
                const abo = await registrierung?.pushManager.getSubscription();
                if (abo) {
                    await this.senden('DELETE', urls.loeschen, { endpoint: abo.endpoint });
                    await abo.unsubscribe();
                }
                this.zustand = 'aus';
                this.meldung = '';
            } finally {
                this.laeuft = false;
            }
        },

        async testen() {
            const antwort = await this.senden('POST', urls.test, {});
            this.meldung = antwort.ok && (await antwort.json()).ok
                ? 'Testnachricht ist unterwegs.'
                : 'Testnachricht konnte nicht zugestellt werden.';
        },

        senden(methode, url, daten) {
            return fetch(url, {
                method: methode,
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
                },
                body: JSON.stringify(daten),
            });
        },
    };
}
