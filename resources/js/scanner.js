// Kamera-Scan für QR-Codes und Barcodes über die BarcodeDetector-Schnittstelle des Browsers
// (Chrome/Edge auf Android und Desktop). Fehlt sie, bleibt das Eingabefeld – ein Handscanner
// funktioniert dort wie eine Tastatur.

export function scanner({ ziel }) {
    return {
        verfuegbar: 'BarcodeDetector' in window && !!navigator.mediaDevices?.getUserMedia,
        aktiv: false,
        stream: null,

        async starten() {
            if (!this.verfuegbar) return;
            const detector = new window.BarcodeDetector({ formats: ['qr_code', 'code_128', 'ean_13'] });
            this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
            this.aktiv = true;
            await this.$nextTick();
            const video = this.$refs.video;
            video.srcObject = this.stream;
            await video.play();

            const suchen = async () => {
                if (!this.aktiv) return;
                const treffer = await detector.detect(video).catch(() => []);
                if (treffer.length) {
                    this.stoppen();
                    const feld = document.querySelector(ziel);
                    feld.value = treffer[0].rawValue;
                    feld.dispatchEvent(new Event('input'));
                    feld.form ? feld.form.requestSubmit() : feld.dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter' }));
                    return;
                }
                requestAnimationFrame(suchen);
            };
            suchen();
        },

        stoppen() {
            this.aktiv = false;
            this.stream?.getTracks().forEach((t) => t.stop());
            this.stream = null;
        },
    };
}
