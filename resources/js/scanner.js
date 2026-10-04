// Kamera-Scan für QR-Codes (Tablet: Annahme, Rückpacken, Ausgabe). Nutzt die Bibliothek qr-scanner,
// die auf allen Geräten funktioniert (auch Windows und iPhone). Ein Handscanner funktioniert
// zusätzlich wie eine Tastatur im Suchfeld.

import QrScanner from 'qr-scanner';

export function scanner({ ziel }) {
    return {
        verfuegbar: !!navigator.mediaDevices?.getUserMedia,
        aktiv: false,
        _scanner: null,

        async starten() {
            if (this._scanner) return this.stoppen();
            this.aktiv = true;
            await this.$nextTick();
            this._scanner = new QrScanner(this.$refs.video, (ergebnis) => {
                this.stoppen();
                const feld = document.querySelector(ziel);
                feld.value = ergebnis.data;
                feld.dispatchEvent(new Event('input'));
                feld.form ? feld.form.requestSubmit() : feld.dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter' }));
            }, { returnDetailedScanResult: true, highlightScanRegion: true, preferredCamera: 'environment' });
            try {
                await this._scanner.start();
            } catch {
                this.stoppen();
                alert('Kamera nicht verfügbar. Bitte Zugriff erlauben oder Nummer eintippen.');
            }
        },

        stoppen() {
            this._scanner?.stop();
            this._scanner?.destroy();
            this._scanner = null;
            this.aktiv = false;
        },
    };
}
