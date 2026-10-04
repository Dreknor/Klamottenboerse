// Kasse: läuft im Browser weiter, auch wenn das Netz weg ist.
// Abgeschlossene Bons landen zuerst im lokalen Speicher und werden im Hintergrund
// an den Server übertragen. Der Server speichert jeden Bon nur einmal (UUID).

const SPEICHER_BONS = 'kasse.offeneBons';
const SPEICHER_DATEN = 'kasse.daten';
const SPEICHER_VERKAUFT = 'kasse.verkauft';

const lesen = (schluessel, standard) => {
    try {
        return JSON.parse(localStorage.getItem(schluessel)) ?? standard;
    } catch {
        return standard;
    }
};
const schreiben = (schluessel, wert) => localStorage.setItem(schluessel, JSON.stringify(wert));

export const euro = (cent) => (cent / 100).toLocaleString('de-DE', { style: 'currency', currency: 'EUR' });

export function kasse({ datenUrl, syncUrl, stornoUrl, tokenUrl, boerseId }) {
    return {
        kassenname: localStorage.getItem('kasse.name') || '',
        positionen: [],
        eingabe: '',
        nummer: '',
        artikel: '',
        preis: '',
        gegeben: '',
        meldung: null,
        modus: 'erfassen', // erfassen | bezahlen
        offeneBons: lesen(SPEICHER_BONS, []),
        daten: lesen(SPEICHER_DATEN, { boerse_id: null, nummern: [], artikel: {} }),
        verkauft: lesen(SPEICHER_VERKAUFT, {}),
        online: navigator.onLine,
        letzterBon: null,

        init() {
            if (this.daten.boerse_id !== boerseId) {
                this.daten = { boerse_id: boerseId, nummern: [], artikel: {} };
                this.verkauft = {};
                schreiben(SPEICHER_VERKAUFT, this.verkauft);
            }
            this.datenLaden();
            this.synchronisieren();
            setInterval(() => this.synchronisieren(), 5000);
            setInterval(() => this.datenLaden(), 60000);
            window.addEventListener('online', () => { this.online = true; this.synchronisieren(); });
            window.addEventListener('offline', () => { this.online = false; });
            this.$nextTick(() => this.$refs.scan?.focus());

            // Seite und Dateien im Browser ablegen, damit die Kasse auch ohne Netz startet
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/kasse-sw.js', { scope: '/kasse' }).catch(() => {});
            }
        },

        // Nach längerer Offline-Phase ist der Sicherheits-Token der (gespeicherten) Seite evtl. veraltet.
        async tokenErneuern() {
            try {
                const antwort = await fetch(tokenUrl, { headers: { Accept: 'application/json' } });
                if (!antwort.ok) return false;
                const { token } = await antwort.json();
                document.querySelector('meta[name=csrf-token]')?.setAttribute('content', token);
                return true;
            } catch {
                return false;
            }
        },

        get summe() {
            return this.positionen.reduce((s, p) => s + p.preis_cent, 0);
        },
        get wechselgeld() {
            const gegeben = this.centAus(this.gegeben);
            return gegeben === null ? null : gegeben - this.summe;
        },
        euro,

        centAus(text) {
            if (text === '' || text === null) return null;
            const zahl = Number(String(text).replace(/\./g, '').replace(',', '.'));
            return Number.isFinite(zahl) ? Math.round(zahl * 100) : null;
        },

        hinweis(text, art = 'fehler') {
            this.meldung = { text, art };
            if (art === 'fehler' && navigator.vibrate) navigator.vibrate(200);
            clearTimeout(this._meldungTimer);
            this._meldungTimer = setTimeout(() => (this.meldung = null), 4000);
        },

        // Barcode vom Handscanner (tippt die Ziffern + Enter) oder Kamera
        scannen() {
            const code = this.eingabe.trim();
            this.eingabe = '';
            if (!/^\d{11}$/.test(code)) {
                this.hinweis('Barcode nicht erkannt. Bitte Nummer, Artikel und Preis eintippen.');
                return;
            }
            this.hinzufuegen(Number(code.slice(0, 3)), Number(code.slice(3, 6)), Number(code.slice(6)));
        },

        manuell() {
            const preis = this.centAus(this.preis);
            if (!this.nummer || !this.artikel || !preis || preis <= 0) {
                this.hinweis('Bitte Nummer, Artikel und Preis ausfüllen.');
                return;
            }
            if (this.hinzufuegen(Number(this.nummer), Number(this.artikel), preis)) {
                this.nummer = this.artikel = this.preis = '';
                this.$refs.nummer?.focus();
            }
        },

        hinzufuegen(nummer, artikel, preis_cent) {
            if (this.daten.nummern.length && !this.daten.nummern.includes(nummer)) {
                this.hinweis(`Verkäufernummer ${nummer} ist nicht vergeben. Etikett prüfen!`);
                return false;
            }
            const schluessel = `${nummer}-${artikel}`;
            if (this.positionen.some((p) => `${p.nummer}-${p.artikel}` === schluessel)) {
                this.hinweis(`Artikel ${schluessel} ist schon auf diesem Bon.`);
                return false;
            }
            if (this.verkauft[schluessel]) {
                this.hinweis(`Achtung: Artikel ${schluessel} wurde an dieser Kasse schon verkauft.`, 'warnung');
            }
            const erfasst = this.daten.artikel[schluessel];
            if (erfasst !== undefined && erfasst !== preis_cent) {
                this.hinweis(`Preis weicht vom erfassten Preis ab (${euro(erfasst)}).`, 'warnung');
            }
            this.positionen.unshift({ nummer, artikel, preis_cent });
            return true;
        },

        entfernen(index) {
            this.positionen.splice(index, 1);
        },

        zurBezahlung() {
            if (!this.positionen.length) return;
            this.modus = 'bezahlen';
            this.gegeben = '';
            this.$nextTick(() => this.$refs.gegeben?.focus());
        },

        abschliessen() {
            const bon = {
                uuid: crypto.randomUUID(),
                erstellt_am: new Date().toISOString(),
                kasse: this.kassenname,
                positionen: this.positionen.map(({ nummer, artikel, preis_cent }) => ({ nummer, artikel, preis_cent })),
            };
            this.offeneBons.push(bon);
            schreiben(SPEICHER_BONS, this.offeneBons);
            bon.positionen.forEach((p) => (this.verkauft[`${p.nummer}-${p.artikel}`] = true));
            schreiben(SPEICHER_VERKAUFT, this.verkauft);

            this.letzterBon = { uuid: bon.uuid, summe: this.summe, wechselgeld: this.wechselgeld };
            this.positionen = [];
            this.modus = 'erfassen';
            this.synchronisieren();
            this.$nextTick(() => this.$refs.scan?.focus());
        },

        // Letzten Bon stornieren (z. B. Kunde hat es sich anders überlegt)
        async stornieren() {
            if (!this.letzterBon) return;
            const grund = prompt('Grund für den Storno (z. B. Kunde hat zurückgegeben):');
            if (!grund) return;
            const uuid = this.letzterBon.uuid;
            if (this.offeneBons.some((b) => b.uuid === uuid)) {
                this.offeneBons = this.offeneBons.filter((b) => b.uuid !== uuid);
                schreiben(SPEICHER_BONS, this.offeneBons);
            } else {
                const token = document.querySelector('meta[name=csrf-token]')?.content;
                const antwort = await fetch(stornoUrl.replace('__UUID__', uuid), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify({ grund }),
                }).catch(() => null);
                if (!antwort?.ok) {
                    this.hinweis('Storno nicht möglich (offline?). Bitte Orga-Team holen.');
                    return;
                }
            }
            this.hinweis('Letzter Einkauf wurde storniert.', 'warnung');
            this.letzterBon = null;
        },

        abbrechen() {
            if (this.positionen.length && !confirm('Diesen Bon wirklich verwerfen?')) return;
            this.positionen = [];
            this.modus = 'erfassen';
        },

        async datenLaden() {
            try {
                const antwort = await fetch(datenUrl, { headers: { Accept: 'application/json' } });
                if (antwort.ok) {
                    this.daten = await antwort.json();
                    schreiben(SPEICHER_DATEN, this.daten);
                }
            } catch {
                // offline – mit den gespeicherten Daten weiterarbeiten
            }
        },

        async synchronisieren() {
            if (this._sync || !this.offeneBons.length) return;
            this._sync = true;
            try {
                for (const bon of [...this.offeneBons]) {
                    const token = document.querySelector('meta[name=csrf-token]')?.content;
                    const antwort = await fetch(syncUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': token },
                        body: JSON.stringify(bon),
                    });
                    if (antwort.ok) {
                        this.offeneBons = this.offeneBons.filter((b) => b.uuid !== bon.uuid);
                        schreiben(SPEICHER_BONS, this.offeneBons);
                    } else if (antwort.status === 422) {
                        const fehler = await antwort.json();
                        this.hinweis(`Bon konnte nicht gespeichert werden: ${fehler.message}. Bitte Orga-Team holen.`);
                        break;
                    } else if (antwort.status === 419 && (await this.tokenErneuern())) {
                        break; // nächster Versuch in wenigen Sekunden mit neuem Token
                    } else if (antwort.status === 419 || antwort.status === 401) {
                        this.hinweis('Sitzung abgelaufen – bitte neu anmelden. Offene Bons bleiben gespeichert.');
                        break;
                    } else {
                        break;
                    }
                }
                this.online = true;
            } catch {
                this.online = false;
            } finally {
                this._sync = false;
            }
        },
    };
}
