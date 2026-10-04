// Kasse: Der offene Einkauf liegt in der Datenbank (je Konto). So kann man am Handy scannen
// und am PC kassieren – alle Geräte mit demselben Konto zeigen denselben Warenkorb.
// Ohne Netz arbeitet die Kasse lokal weiter: Änderungen warten in einer Warteschlange,
// offline abgeschlossene Einkäufe werden später übertragen (Server speichert jeden Bon nur einmal).

import QrScanner from 'qr-scanner';

const SPEICHER = {
    warteschlange: 'kasse.warteschlange',
    bons: 'kasse.offeneBons',
    daten: 'kasse.daten',
    positionen: 'kasse.positionen',
};

const lesen = (schluessel, standard) => {
    try {
        return JSON.parse(localStorage.getItem(schluessel)) ?? standard;
    } catch {
        return standard;
    }
};
const schreiben = (schluessel, wert) => {
    try {
        localStorage.setItem(schluessel, JSON.stringify(wert));
    } catch {
        // Speicher voll oder gesperrt – die Kasse läuft trotzdem online weiter
    }
};

export const euro = (cent) => (cent / 100).toLocaleString('de-DE', { style: 'currency', currency: 'EUR' });

// crypto.randomUUID gibt es nur über HTTPS/localhost – im WLAN über http braucht es den Ersatz.
function neueUuid() {
    if (crypto.randomUUID) return crypto.randomUUID();
    const b = crypto.getRandomValues(new Uint8Array(16));
    b[6] = (b[6] & 0x0f) | 0x40;
    b[8] = (b[8] & 0x3f) | 0x80;
    const h = [...b].map((x) => x.toString(16).padStart(2, '0')).join('');
    return `${h.slice(0, 8)}-${h.slice(8, 12)}-${h.slice(12, 16)}-${h.slice(16, 20)}-${h.slice(20)}`;
}

export function kasse({ urls, boerseId }) {
    return {
        kassenname: localStorage.getItem('kasse.name') || '',
        positionen: lesen(SPEICHER.positionen, []),
        warteschlange: lesen(SPEICHER.warteschlange, []),
        offeneBons: lesen(SPEICHER.bons, []),
        daten: lesen(SPEICHER.daten, { boerse_id: null, nummern: [], artikel: {} }),
        nummer: '',
        artikel: '',
        preis: '',
        gegeben: '',
        meldung: null,
        modus: 'erfassen', // erfassen | bezahlen
        online: navigator.onLine,
        letzterBon: null,
        kamera: false,
        euro,

        init() {
            if (this.daten.boerse_id !== boerseId) {
                this.daten = { boerse_id: boerseId, nummern: [], artikel: {} };
            }
            this.datenLaden();
            this.abgleichen();
            setInterval(() => this.abgleichen(), 2000);
            setInterval(() => this.datenLaden(), 60000);
            window.addEventListener('online', () => { this.online = true; this.abgleichen(); });
            window.addEventListener('offline', () => { this.online = false; });
            document.addEventListener('visibilitychange', () => document.visibilityState === 'visible' && this.abgleichen());
            this.$nextTick(() => this.$refs.nummer?.focus());

            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/kasse-sw.js', { scope: '/kasse' }).catch(() => {});
            }
        },

        get summe() {
            return this.positionen.reduce((s, p) => s + p.preis_cent, 0);
        },
        get wechselgeld() {
            const gegeben = this.centAus(this.gegeben);
            return gegeben === null ? null : gegeben - this.summe;
        },
        get ungesendet() {
            return this.warteschlange.length + this.offeneBons.length;
        },

        centAus(text) {
            if (text === '' || text === null) return null;
            const zahl = Number(String(text).replace(/\./g, '').replace(',', '.'));
            return Number.isFinite(zahl) ? Math.round(zahl * 100) : null;
        },

        hinweis(text, art = 'fehler') {
            this.meldung = { text, art };
            if (art === 'fehler' && navigator.vibrate) navigator.vibrate(200);
            clearTimeout(this._meldungTimer);
            this._meldungTimer = setTimeout(() => (this.meldung = null), 4500);
        },

        speichern() {
            schreiben(SPEICHER.positionen, this.positionen);
            schreiben(SPEICHER.warteschlange, this.warteschlange);
            schreiben(SPEICHER.bons, this.offeneBons);
        },

        // ---------- Hand-Eingabe: Nummer → Artikel → Preis, Enter springt weiter ----------

        // Handscanner tippen den 11-stelligen Code ins Nummernfeld – der wird sofort erfasst.
        nummerEingabe() {
            const wert = this.nummer.trim();
            if (/^\d{11}$/.test(wert)) {
                this.nummer = '';
                this.codeGelesen(wert);
            }
        },

        nummerEnter() {
            const wert = this.nummer.trim();
            if (/^\d{11}$/.test(wert)) {
                this.nummer = '';
                this.codeGelesen(wert);
            } else if (wert) {
                this.$refs.artikel.focus();
            }
        },

        artikelEnter() {
            if (this.artikel.trim()) this.$refs.preis.focus();
        },

        preisEnter() {
            if (!this.nummer) return this.$refs.nummer.focus();
            if (!this.artikel) return this.$refs.artikel.focus();
            this.handAbsenden();
        },

        handAbsenden() {
            const preis = this.centAus(this.preis);
            if (!this.nummer || !this.artikel || !preis || preis <= 0) {
                this.hinweis('Bitte Nummer, Artikel und Preis ausfüllen.');
                return;
            }
            if (this.hinzufuegen(Number(this.nummer), Number(this.artikel), preis)) {
                this.nummer = this.artikel = this.preis = '';
            }
            this.$nextTick(() => this.$refs.nummer.focus());
        },

        // ---------- Scannen (Handscanner, Kamera) ----------

        codeGelesen(code) {
            if (!/^\d{11}$/.test(code)) {
                this.hinweis('Code nicht erkannt. Bitte Nummer, Artikel und Preis eintippen.');
                return;
            }
            this.hinzufuegen(Number(code.slice(0, 3)), Number(code.slice(3, 6)), Number(code.slice(6)));
            this.$nextTick(() => this.$refs.nummer?.focus());
        },

        async kameraStarten() {
            if (this._scanner) {
                this.kameraStoppen();
                return;
            }
            this.kamera = true;
            await this.$nextTick();
            let zuletzt = { code: null, zeit: 0 };
            this._scanner = new QrScanner(this.$refs.video, (ergebnis) => {
                const jetzt = Date.now();
                // gleiches Etikett nicht mehrfach erfassen, solange es vor der Kamera bleibt
                if (ergebnis.data === zuletzt.code && jetzt - zuletzt.zeit < 2500) return;
                zuletzt = { code: ergebnis.data, zeit: jetzt };
                if (navigator.vibrate) navigator.vibrate(60);
                this.codeGelesen(ergebnis.data);
            }, { returnDetailedScanResult: true, highlightScanRegion: true, preferredCamera: 'environment' });
            try {
                await this._scanner.start();
            } catch {
                this.hinweis('Kamera nicht verfügbar. Bitte Zugriff erlauben oder Code eintippen.');
                this.kameraStoppen();
            }
        },

        kameraStoppen() {
            this._scanner?.stop();
            this._scanner?.destroy();
            this._scanner = null;
            this.kamera = false;
        },

        // ---------- Warenkorb ----------

        hinzufuegen(nummer, artikel, preis_cent) {
            if (this.daten.nummern.length && !this.daten.nummern.includes(nummer)) {
                this.hinweis(`Verkäufernummer ${nummer} ist nicht vergeben. Etikett prüfen!`);
                return false;
            }
            if (this.positionen.some((p) => p.nummer === nummer && p.artikel === artikel)) {
                this.hinweis(`Artikel ${nummer}-${artikel} ist schon in diesem Einkauf.`);
                return false;
            }
            const erfasst = this.daten.artikel[`${nummer}-${artikel}`];
            if (erfasst !== undefined && erfasst !== preis_cent) {
                this.hinweis(`Preis weicht vom erfassten Preis ab (${euro(erfasst)}).`, 'warnung');
            }
            const position = { uuid: neueUuid(), nummer, artikel, preis_cent, wartet: true };
            this.positionen.unshift(position);
            this.warteschlange.push({ art: 'hinzufuegen', position });
            this.speichern();
            this.abgleichen();
            return true;
        },

        entfernen(uuid) {
            this.positionen = this.positionen.filter((p) => p.uuid !== uuid);
            const warteteNoch = this.warteschlange.some((o) => o.art === 'hinzufuegen' && o.position.uuid === uuid);
            this.warteschlange = warteteNoch
                ? this.warteschlange.filter((o) => !(o.art === 'hinzufuegen' && o.position.uuid === uuid))
                : [...this.warteschlange, { art: 'entfernen', uuid }];
            this.speichern();
            this.abgleichen();
        },

        abbrechen() {
            if (this.positionen.length && !confirm('Diesen Einkauf wirklich verwerfen?')) return;
            [...this.positionen].forEach((p) => this.entfernen(p.uuid));
            this.modus = 'erfassen';
            this.$nextTick(() => this.$refs.nummer?.focus());
        },

        zurBezahlung() {
            if (!this.positionen.length) return;
            this.modus = 'bezahlen';
            this.gegeben = '';
            this.$nextTick(() => this.$refs.gegeben?.focus());
        },

        async abschliessen() {
            if (!this.positionen.length) return;
            const summe = this.summe;
            const wechselgeld = this.wechselgeld;
            const bonUuid = neueUuid();

            await this.abgleichen();
            const antwort = this.online && !this.warteschlange.length
                ? await this.anfrage('POST', urls.abschliessen, { bon_uuid: bonUuid, kasse: this.kassenname })
                : null;

            if (antwort?.ok) {
                this.uebernehmen(await antwort.json());
            } else if (antwort && antwort.status === 422) {
                this.hinweis((await antwort.json()).message);
                return;
            } else {
                // offline: Einkauf lokal abschließen und später übertragen
                const positionen = this.positionen.map(({ uuid, nummer, artikel, preis_cent }) => ({ uuid, nummer, artikel, preis_cent }));
                const uuids = positionen.map((p) => p.uuid);
                this.offeneBons.push({ uuid: bonUuid, erstellt_am: new Date().toISOString(), kasse: this.kassenname, positionen });
                this.warteschlange = this.warteschlange.filter((o) => !(o.art === 'hinzufuegen' && uuids.includes(o.position.uuid)));
                this.positionen = [];
                this.speichern();
            }

            this.letzterBon = { uuid: bonUuid, summe_cent: summe, wechselgeld, um: new Date().toTimeString().slice(0, 5) };
            this.modus = 'erfassen';
            this.$nextTick(() => this.$refs.nummer?.focus());
        },

        async stornieren() {
            if (!this.letzterBon) return;
            const grund = prompt('Grund für den Storno (z. B. Kunde hat zurückgegeben):');
            if (!grund) return;
            const uuid = this.letzterBon.uuid;
            if (this.offeneBons.some((b) => b.uuid === uuid)) {
                this.offeneBons = this.offeneBons.filter((b) => b.uuid !== uuid);
                this.speichern();
            } else {
                const antwort = await this.anfrage('POST', urls.storno.replace('__UUID__', uuid), { grund });
                if (!antwort?.ok) {
                    this.hinweis('Storno nicht möglich (offline?). Bitte Orga-Team holen.');
                    return;
                }
            }
            this.hinweis('Letzter Einkauf wurde storniert.', 'warnung');
            this.letzterBon = null;
        },

        // ---------- Abgleich mit dem Server ----------

        async anfrage(methode, url, daten = null, erneut = true) {
            try {
                const antwort = await fetch(url, {
                    method: methode,
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
                    },
                    body: daten ? JSON.stringify(daten) : null,
                });
                if (antwort.status === 419 && erneut && (await this.tokenErneuern())) {
                    return this.anfrage(methode, url, daten, false);
                }
                if (antwort.status === 401 || antwort.status === 419) {
                    this.hinweis('Sitzung abgelaufen – bitte neu anmelden. Nichts geht verloren.');
                }
                this.online = true;
                return antwort;
            } catch {
                this.online = false;
                return null;
            }
        },

        // Nach längerer Offline-Phase ist der Sicherheits-Token der (gespeicherten) Seite evtl. veraltet.
        async tokenErneuern() {
            try {
                const antwort = await fetch(urls.token, { headers: { Accept: 'application/json' } });
                if (!antwort.ok) return false;
                const { token } = await antwort.json();
                document.querySelector('meta[name=csrf-token]')?.setAttribute('content', token);
                return true;
            } catch {
                return false;
            }
        },

        // Serverstand übernehmen; noch nicht übertragene eigene Artikel bleiben oben stehen.
        uebernehmen(stand) {
            const wartend = this.warteschlange.filter((o) => o.art === 'hinzufuegen').map((o) => o.position);
            const entfernt = this.warteschlange.filter((o) => o.art === 'entfernen').map((o) => o.uuid);
            this.positionen = [...wartend, ...stand.positionen.filter((p) => !entfernt.includes(p.uuid) && !wartend.some((w) => w.uuid === p.uuid))];
            if (stand.letzter_bon && stand.letzter_bon.uuid !== this.letzterBon?.uuid) {
                this.letzterBon = { ...stand.letzter_bon, wechselgeld: null };
                if (this.modus === 'bezahlen' && !this.positionen.length) this.modus = 'erfassen';
            }
            this.speichern();
        },

        async abgleichen() {
            if (this._abgleich) return;
            this._abgleich = true;
            try {
                // 1. Wartende Änderungen der Reihe nach senden
                while (this.warteschlange.length) {
                    const op = this.warteschlange[0];
                    const antwort = op.art === 'hinzufuegen'
                        ? await this.anfrage('POST', urls.warenkorb, op.position)
                        : await this.anfrage('DELETE', `${urls.warenkorb}/${op.uuid}`);
                    if (!antwort) return; // offline – später erneut
                    if (antwort.status === 422) {
                        this.hinweis((await antwort.json()).message);
                        this.positionen = this.positionen.filter((p) => p.uuid !== op.position?.uuid);
                    } else if (!antwort.ok) {
                        return;
                    } else if (op.art === 'hinzufuegen') {
                        (await antwort.json()).warnungen?.forEach((w) => this.hinweis(w, 'warnung'));
                    }
                    this.warteschlange.shift();
                    this.speichern();
                }

                // 2. Offline abgeschlossene Einkäufe übertragen
                for (const bon of [...this.offeneBons]) {
                    const antwort = await this.anfrage('POST', urls.sync, bon);
                    if (!antwort) return;
                    if (antwort.ok) {
                        this.offeneBons = this.offeneBons.filter((b) => b.uuid !== bon.uuid);
                        this.speichern();
                    } else if (antwort.status === 422) {
                        this.hinweis(`Einkauf konnte nicht gespeichert werden: ${(await antwort.json()).message}. Bitte Orga-Team holen.`);
                        return;
                    } else {
                        return;
                    }
                }

                // 3. Aktuellen Stand holen (Änderungen von anderen Geräten desselben Kontos)
                const antwort = await this.anfrage('GET', urls.warenkorb);
                if (antwort?.ok) this.uebernehmen(await antwort.json());
            } finally {
                this._abgleich = false;
            }
        },

        async datenLaden() {
            const antwort = await this.anfrage('GET', urls.daten);
            if (antwort?.ok) {
                this.daten = await antwort.json();
                schreiben(SPEICHER.daten, this.daten);
            }
        },
    };
}
