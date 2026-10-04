<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Website\Bausteine;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\SeiteController as OeffentlicheSeite;
use App\Models\Seite;
use App\Models\SeitenVersion;
use App\Support\Einstellungen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Website-Seiten: Impressum und Datenschutz als Text, alle anderen aus Bausteinen –
 * mit Entwurf, Vorschau, Veröffentlichen, Versionen, Bildern und Menü.
 */
class SeiteController extends Controller
{
    /** Seiten, die nicht gelöscht werden dürfen. */
    public const SYSTEM = ['start', 'impressum', 'datenschutz'];

    public function index(): View
    {
        return view('admin.seiten.index', [
            'seiten' => Seite::query()->with('bearbeitetVon')->orderByRaw("slug = 'start' desc")->orderByDesc('im_menue')->orderBy('menue_reihenfolge')->orderBy('titel')->get(),
            'unvollstaendig' => Seite::betreiberUnvollstaendig(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $daten = $request->validate(['titel' => ['required', 'string', 'max:120']]);
        $slug = Str::slug($daten['titel'], '-', 'de');
        if ($slug === '' || in_array($slug, Seite::RESERVIERT, true) || Seite::query()->where('slug', $slug)->exists()) {
            return back()->withInput()->withErrors(['titel' => 'Diese Adresse ist schon vergeben – bitte einen anderen Titel wählen.']);
        }

        $seite = Seite::create([
            'slug' => $slug, 'titel' => $daten['titel'], 'bloecke' => null,
            'entwurf' => ['titel' => $daten['titel'], 'bloecke' => [['typ' => 'text', 'titel' => '', 'text' => '']]],
        ]);

        return redirect()->route('admin.seiten.edit', $seite)->with('erfolg', 'Seite angelegt. Sie ist erst nach dem Veröffentlichen sichtbar.');
    }

    public function edit(Seite $seite): View
    {
        if (! $seite->istBausteinSeite()) {
            return view('admin.seiten.edit', [
                'seite' => $seite,
                'platzhalter' => Einstellungen::BETREIBER,
                'unvollstaendig' => Seite::betreiberUnvollstaendig(),
            ]);
        }

        $stand = $seite->entwurf ?? ['titel' => $seite->titel, 'bloecke' => $seite->bloecke ?? []];

        return view('admin.seiten.bausteine', [
            'seite' => $seite,
            'stand' => $stand,
            'typen' => Bausteine::TYPEN,
            'knopfZiele' => Bausteine::KNOPF_ZIELE,
            'bilder' => $seite->getMedia('bilder')->map(fn (Media $m) => [
                'id' => $m->id, 'url' => $m->getUrl('klein'), 'alt' => $m->getCustomProperty('alt'), 'name' => $m->file_name,
            ])->values(),
            'versionen' => $seite->versionen()->with('ersteller')->limit(15)->get(),
        ]);
    }

    /** Text-Seiten direkt speichern, Baustein-Seiten als Entwurf. */
    public function update(Request $request, Seite $seite): RedirectResponse
    {
        if (! $seite->istBausteinSeite()) {
            $seite->update($request->validate([
                'titel' => ['required', 'string', 'max:120'],
                'inhalt' => ['required', 'string', 'max:100000'],
            ]) + ['bearbeitet_von' => $request->user()->id]);

            return back()->with('erfolg', 'Seite gespeichert und veröffentlicht.');
        }

        $daten = $request->validate([
            'titel' => ['required', 'string', 'max:120'],
            'bloecke' => ['required', 'json'],
        ]);
        $seite->update(['entwurf' => [
            'titel' => $daten['titel'],
            'bloecke' => Bausteine::bereinigen(json_decode($daten['bloecke'], true) ?? []),
        ]]);

        if ($request->input('aktion') === 'veroeffentlichen') {
            $seite->veroeffentlichen($request->user());

            return back()->with('erfolg', 'Veröffentlicht. Die vorherige Fassung ist unter „Versionen“ gesichert.');
        }

        return $request->input('aktion') === 'vorschau'
            ? redirect()->route('admin.seiten.vorschau', $seite)
            : back()->with('erfolg', 'Entwurf gespeichert. Besucher sehen noch die bisherige Fassung.');
    }

    public function vorschau(Seite $seite): View
    {
        $stand = $seite->entwurf ?? ['titel' => $seite->titel, 'bloecke' => $seite->bloecke ?? []];

        return OeffentlicheSeite::bausteinAnsicht($seite, $stand['bloecke'], $stand['titel'], vorschau: true);
    }

    public function entwurfVerwerfen(Seite $seite): RedirectResponse
    {
        if ($seite->bloecke === null) {
            return back()->with('fehler', 'Die Seite wurde noch nie veröffentlicht – es gibt keine Fassung, zu der man zurückkehren könnte.');
        }
        $seite->update(['entwurf' => null]);

        return back()->with('erfolg', 'Entwurf verworfen.');
    }

    /** Eine frühere Fassung als Entwurf laden – veröffentlicht wird erst auf Knopfdruck. */
    public function versionLaden(Seite $seite, SeitenVersion $version): RedirectResponse
    {
        abort_unless($version->seite_id === $seite->id, 404);
        $seite->update(['entwurf' => ['titel' => $version->titel, 'bloecke' => $version->bloecke]]);

        return back()->with('erfolg', 'Fassung vom '.$version->created_at->format('d.m.Y H:i').' als Entwurf geladen. Zum Übernehmen „Veröffentlichen“ klicken.');
    }

    public function einstellungen(Request $request, Seite $seite): RedirectResponse
    {
        $seite->update($request->validate([
            'beschreibung' => ['nullable', 'string', 'max:200'],
            'menue_reihenfolge' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]) + ['im_menue' => $request->boolean('im_menue') && $seite->slug !== 'start' && $seite->veroeffentlicht_at !== null]);

        return back()->with('erfolg', 'Einstellungen gespeichert.');
    }

    public function destroy(Seite $seite): RedirectResponse
    {
        abort_if(in_array($seite->slug, self::SYSTEM, true), 403, 'Diese Seite wird immer gebraucht.');
        $seite->delete();

        return redirect()->route('admin.seiten.index')->with('erfolg', 'Seite gelöscht.');
    }

    public function bildHochladen(Request $request, Seite $seite): RedirectResponse
    {
        $request->validate([
            'bild' => ['required', 'image', 'max:15360'],
            'alt' => ['required', 'string', 'max:200'],
        ], ['alt.required' => 'Bitte beschreibe kurz, was auf dem Bild zu sehen ist (für Menschen, die nicht sehen können).']);

        $seite->addMediaFromRequest('bild')->withCustomProperties(['alt' => $request->input('alt')])->toMediaCollection('bilder');

        return back()->with('erfolg', 'Bild hochgeladen und verkleinert. Du kannst es jetzt in Bild- oder Galerie-Bausteinen auswählen.');
    }

    public function bildLoeschen(Seite $seite, Media $media): RedirectResponse
    {
        abort_unless($media->model_id === $seite->id && $media->model_type === $seite->getMorphClass(), 404);
        $media->delete();

        return back()->with('erfolg', 'Bild gelöscht.');
    }
}
