<x-layouts.admin titel="Personen">
    <x-ui.kopf titel="Personen" :unter="count($personen).' Personen – Verkäufer, Helfer und Team in einer Kartei.'">
        <x-ui.knopf :href="route('admin.personen.create')">Neue Person</x-ui.knopf>
    </x-ui.kopf>

    <div x-data="personenListe(@js($personen))"><x-ui.karte>
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <input x-model="suche" type="search" class="feld max-w-md" autofocus
                   placeholder="Tippen zum Suchen: Name, E-Mail, Telefon{{ $boerse ? ' oder Nummer' : '' }}" aria-label="Personen durchsuchen">
            <div class="flex flex-wrap gap-1 text-sm">
                <template x-for="[wert, text] in filterliste" :key="wert">
                    <button type="button" class="rounded-full border px-3 py-1"
                            :class="filter === wert ? 'border-marke-600 bg-marke-50 text-marke-800' : 'border-stone-300 text-stone-600 hover:bg-stone-100'"
                            @click="filter = wert" x-text="text"></button>
                </template>
            </div>
            <span class="ml-auto text-sm text-stone-500" x-text="treffer.length + ' Treffer'"></span>
        </div>
        <div class="overflow-x-auto">
            <table class="tabelle">
                <thead><tr><th>Name</th><th>E-Mail</th><th>Telefon</th>@if ($boerse)<th>Nr.</th>@endif<th>Börsen</th><th>Team</th></tr></thead>
                <tbody>
                <template x-for="p in sichtbar" :key="p.id">
                    <tr class="cursor-pointer hover:bg-stone-50" @click="location = p.url">
                        <td><a :href="p.url" x-text="p.name"></a> <span class="text-xs text-stone-500" x-text="p.kinderhaus"></span></td>
                        <td x-text="p.email"></td>
                        <td x-text="p.telefon"></td>
                        @if ($boerse)<td class="font-mono" x-text="p.nummer ?? (p.angemeldet ? 'Warteliste' : '')"></td>@endif
                        <td x-text="p.boersen"></td>
                        <td><template x-for="r in p.rollen"><span class="mr-1 rounded-full bg-stone-100 px-2 py-0.5 text-xs" x-text="r"></span></template></td>
                    </tr>
                </template>
                <tr x-show="!treffer.length"><td colspan="6" class="text-stone-500">Niemand gefunden.</td></tr>
                </tbody>
            </table>
        </div>
        <button type="button" class="mt-3 text-sm text-marke-700 hover:underline" x-show="treffer.length > sichtbar.length" @click="grenze += 200"
                x-text="'Weitere ' + Math.min(200, treffer.length - sichtbar.length) + ' anzeigen'"></button>
    </x-ui.karte></div>

    <script>
        function personenListe(personen) {
            const normal = (t) => (t ?? '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            personen.forEach((p) => (p._text = normal([p.name, p.email, p.telefon, p.nummer].join(' '))));
            return {
                personen,
                suche: '',
                filter: 'alle',
                grenze: 200,
                filterliste: [['alle', 'Alle'], @if ($boerse)['angemeldet', 'Bei dieser Börse'], @endif['team', 'Team'], ['ohne_mail', 'Ohne E-Mail']],
                init() {
                    this.$watch('suche', () => (this.grenze = 200));
                    this.$watch('filter', () => (this.grenze = 200));
                },
                get treffer() {
                    const woerter = normal(this.suche).split(/\s+/).filter(Boolean);
                    return this.personen.filter((p) =>
                        (this.filter !== 'angemeldet' || p.angemeldet)
                        && (this.filter !== 'team' || p.rollen.length)
                        && (this.filter !== 'ohne_mail' || !p.email)
                        && woerter.every((w) => p._text.includes(w)));
                },
                get sichtbar() {
                    return this.treffer.slice(0, this.grenze);
                },
            };
        }
    </script>
</x-layouts.admin>
