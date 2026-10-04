{{--
    Cookie-Hinweis: Die Website setzt nur technisch notwendige Cookies (Sitzung, Formularschutz).
    Dafür ist keine Einwilligung nötig (§ 25 Abs. 2 TDDDG) – wir informieren trotzdem transparent.
    Sollten später einwilligungspflichtige Dienste dazukommen, gehören sie als weitere Kategorie hierher
    und dürfen erst nach Zustimmung (einstellungen.extern === true) geladen werden.
--}}
<div x-data="{
        offen: false,
        details: false,
        init() {
            let gespeichert = null;
            try { gespeichert = JSON.parse(localStorage.getItem('cookie-einstellungen')); } catch (e) {}
            this.offen = !gespeichert;
            window.addEventListener('cookie-einstellungen-oeffnen', () => { this.offen = true; this.details = true; });
        },
        speichern() {
            try { localStorage.setItem('cookie-einstellungen', JSON.stringify({ notwendig: true, datum: new Date().toISOString() })); } catch (e) {}
            this.offen = false;
        },
    }"
     x-show="offen" x-cloak
     class="fixed inset-x-0 bottom-0 z-50 p-3 sm:p-4" role="dialog" aria-modal="false" aria-labelledby="cookie-titel">
    <div class="mx-auto max-w-3xl rounded-2xl border-t-4 border-marke-500 bg-white p-5 shadow-2xl ring-1 ring-stone-200">
        <h2 id="cookie-titel" class="text-base">Cookies auf dieser Website</h2>
        <p class="mt-2 text-sm text-stone-700">
            Wir verwenden nur technisch notwendige Cookies, damit Anmeldung und Formulare funktionieren.
            Es gibt keine Werbe-, Analyse- oder Tracking-Cookies und keine Inhalte fremder Anbieter.
            Mehr dazu in der <a href="{{ route('datenschutz') }}">Datenschutzerklärung</a>.
        </p>

        <div x-show="details" x-cloak class="mt-4 space-y-3 rounded-xl bg-stone-50 p-4 text-sm">
            <label class="flex items-start gap-3">
                <input type="checkbox" checked disabled class="mt-1">
                <span><strong>Notwendig</strong> (immer aktiv)<br>
                    Sitzungs-Cookie <code>klamottenboerse_session</code> und Sicherheits-Cookie <code>XSRF-TOKEN</code> – werden beim Schließen des Browsers bzw. nach 2 Stunden ungültig.
                    Im Browser-Speicher: diese Auswahl; an der Kasse noch nicht übertragene Verkäufe.</span>
            </label>
            <p class="text-stone-500">Weitere Kategorien (Statistik, Marketing, externe Medien) nutzen wir nicht.</p>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-3">
            <button type="button" @click="speichern()" class="rounded-lg bg-marke-600 px-5 py-2 font-medium text-white hover:bg-marke-700">Verstanden</button>
            <button type="button" @click="details = !details" class="text-sm text-marke-700 underline" x-text="details ? 'Details ausblenden' : 'Details anzeigen'"></button>
        </div>
    </div>
</div>
