<x-layouts.admin titel="Ablage">
    <x-ui.kopf :titel="$ordner?->name ?? 'Team-Ablage'" unter="Dokumente, Bilder und Vorlagen des Teams. Ordner können für Helfer im Portal freigegeben werden.">
        <x-ui.knopf art="sekundaer" :href="route('admin.protokolle.index')">Protokolle</x-ui.knopf>
    </x-ui.kopf>

    {{-- Brotkrumen --}}
    <nav class="mb-4 flex flex-wrap items-center gap-1 text-sm" aria-label="Ordnerpfad">
        <a href="{{ route('admin.ablage.index') }}">Ablage</a>
        @foreach ($pfad as $teil)
            <span class="text-stone-400">/</span>
            @if ($loop->last)<span class="font-medium">{{ $teil->name }}</span>@else<a href="{{ route('admin.ablage.ordner', $teil) }}">{{ $teil->name }}</a>@endif
        @endforeach
    </nav>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            @if ($unterordner->isNotEmpty())
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach ($unterordner as $o)
                        <a href="{{ route('admin.ablage.ordner', $o) }}" class="rounded-xl bg-white p-4 text-stone-800 no-underline shadow-sm ring-1 ring-stone-200 hover:ring-marke-500">
                            <span class="text-2xl" aria-hidden="true">📁</span>
                            <span class="mt-1 block font-medium">{{ $o->name }}</span>
                            <span class="text-xs text-stone-500">{{ $o->media_count }} Dateien{{ $o->kinder_count ? ' · '.$o->kinder_count.' Ordner' : '' }}{{ $o->fuer_helfer ? ' · für Helfer' : '' }}</span>
                        </a>
                    @endforeach
                </div>
            @endif

            @if ($ordner)
                <x-ui.karte titel="Dateien">
                    @php $bilder = $dateien->filter(fn ($m) => str_starts_with($m->mime_type, 'image/')); $andere = $dateien->diff($bilder); @endphp
                    @if ($bilder->isNotEmpty())
                        <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                            @foreach ($bilder as $m)
                                <figure class="group relative">
                                    <a href="{{ route('admin.ablage.datei', $m) }}" target="_blank">
                                        <img src="{{ route('admin.ablage.datei', [$m, 'vorschau' => 1]) }}" alt="{{ $m->name }}" class="aspect-square w-full rounded-lg object-cover" loading="lazy">
                                    </a>
                                    <figcaption class="mt-1 truncate text-xs text-stone-600">{{ $m->file_name }}</figcaption>
                                </figure>
                            @endforeach
                        </div>
                    @endif
                    @foreach ($andere as $m)
                        <div class="flex items-center justify-between gap-3 border-b border-stone-100 py-2 last:border-0">
                            <a href="{{ route('admin.ablage.datei', $m) }}" target="_blank" class="truncate">📄 {{ $m->file_name }}</a>
                            <span class="shrink-0 text-xs text-stone-500">{{ $m->human_readable_size }} · {{ $m->created_at->format('d.m.Y') }} · {{ $m->getCustomProperty('hochgeladen_von') }}</span>
                        </div>
                    @endforeach
                    @if ($dateien->isEmpty())
                        <p class="text-stone-500">Noch keine Dateien in diesem Ordner.</p>
                    @endif

                    @if ($dateien->isNotEmpty())
                        <details class="mt-4 text-sm">
                            <summary class="cursor-pointer text-stone-600">Dateien löschen …</summary>
                            @foreach ($dateien as $m)
                                <form method="post" action="{{ route('admin.ablage.datei.loeschen', $m) }}" class="flex items-center justify-between border-b border-stone-100 py-1" onsubmit="return confirm('{{ $m->file_name }} löschen?')">
                                    @csrf @method('delete')
                                    <span class="truncate">{{ $m->file_name }}</span>
                                    <button class="text-red-700 hover:underline">löschen</button>
                                </form>
                            @endforeach
                        </details>
                    @endif
                </x-ui.karte>
            @elseif ($unterordner->isEmpty())
                <p class="text-stone-500">Noch keine Ordner. Lege rechts den ersten an.</p>
            @endif
        </div>

        <div class="space-y-6">
            @if ($ordner)
                <x-ui.karte titel="Hochladen">
                    <form method="post" action="{{ route('admin.ablage.hochladen', $ordner) }}" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <input type="file" name="dateien[]" multiple class="block w-full text-sm" accept="image/*,.pdf,.doc,.docx,.odt,.xls,.xlsx,.ods,.txt,.md,.csv" required>
                        <p class="text-xs text-stone-500">Bis zu 20 MB je Datei. Am Handy kannst du direkt ein Foto aufnehmen.</p>
                        <x-ui.knopf>Hochladen</x-ui.knopf>
                    </form>
                </x-ui.karte>
            @endif

            <x-ui.karte :titel="$ordner ? 'Unterordner anlegen' : 'Ordner anlegen'">
                <form method="post" action="{{ $ordner ? route('admin.ablage.ordner.anlegen', $ordner) : route('admin.ablage.anlegen') }}" class="flex gap-2">
                    @csrf
                    <input name="name" class="feld" placeholder="z. B. Lagepläne" required>
                    <x-ui.knopf>Anlegen</x-ui.knopf>
                </form>
            </x-ui.karte>

            @if ($ordner)
                <x-ui.karte titel="Ordner bearbeiten">
                    <form method="post" action="{{ route('admin.ablage.ordner.aendern', $ordner) }}" class="space-y-3">
                        @csrf @method('put')
                        <x-ui.feld name="name" label="Name" :wert="$ordner->name" required />
                        <label class="flex items-start gap-2 text-sm">
                            <input type="hidden" name="fuer_helfer" value="0">
                            <input type="checkbox" name="fuer_helfer" value="1" class="mt-1" @checked($ordner->fuer_helfer)>
                            Für Helfer im Portal sichtbar (z. B. Lageplan, Aufbauanleitung)
                        </label>
                        <x-ui.knopf art="sekundaer">Speichern</x-ui.knopf>
                    </form>
                    <form method="post" action="{{ route('admin.ablage.ordner.loeschen', $ordner) }}" class="mt-3" onsubmit="return confirm('Leeren Ordner löschen?')">
                        @csrf @method('delete')<button class="text-sm text-red-700 hover:underline">Ordner löschen</button>
                    </form>
                </x-ui.karte>
            @endif
        </div>
    </div>
</x-layouts.admin>
