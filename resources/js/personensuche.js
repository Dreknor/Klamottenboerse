// Live-Personensuche: Tippen → Treffer erscheinen sofort (Name, E-Mail, Telefon oder Nummer).
// Pfeiltasten + Enter wählen aus. Entweder füllt sie ein verstecktes Formularfeld (Auswahl)
// oder springt direkt zur Person (Schnellsuche in der Seitenleiste).

export function personenSuche({ url, springen = false, sperren = false, gewaehlt = null }) {
    return {
        q: '',
        treffer: [],
        offen: false,
        markiert: 0,
        laedt: false,
        gewaehlt,
        _timer: null,
        _anfrage: 0,

        eingabe() {
            clearTimeout(this._timer);
            if (this.q.trim().length < 2) {
                this.treffer = [];
                this.offen = false;
                return;
            }
            this._timer = setTimeout(() => this.laden(), 150);
        },

        async laden() {
            const nr = ++this._anfrage;
            this.laedt = true;
            try {
                const antwort = await fetch(`${url}?q=${encodeURIComponent(this.q.trim())}`, { headers: { Accept: 'application/json' } });
                if (nr !== this._anfrage) return; // eine neuere Suche läuft schon
                this.treffer = antwort.ok ? await antwort.json() : [];
                this.markiert = 0;
                this.offen = true;
            } finally {
                if (nr === this._anfrage) this.laedt = false;
            }
        },

        hoch() {
            this.markiert = Math.max(0, this.markiert - 1);
        },
        runter() {
            this.markiert = Math.min(this.treffer.length - 1, this.markiert + 1);
        },
        enter() {
            if (this.offen && this.treffer[this.markiert]) this.waehlen(this.treffer[this.markiert]);
        },

        waehlen(person) {
            if (sperren && person.angemeldet) return;
            if (springen) {
                window.location = person.url;
                return;
            }
            this.gewaehlt = person;
            this.q = '';
            this.treffer = [];
            this.offen = false;
            this.$dispatch('person-gewaehlt', person);
        },

        zuruecksetzen() {
            this.gewaehlt = null;
            this.$nextTick(() => this.$refs.eingabe?.focus());
        },
    };
}
