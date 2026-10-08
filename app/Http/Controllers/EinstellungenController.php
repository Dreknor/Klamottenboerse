<?php

namespace App\Http\Controllers;

use App\Model\Angebotskategorie;
use App\Model\Einstellung;
use App\Model\VerkaeuferVermerk;
use App\Model\VermerkTyp;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Pflege der teaminternen Einstellungen durch das Orga-Team:
 * Reputation (Schwellen, Vermerk-Arten mit Punkten) und Angebotskategorien.
 * Einträge werden nicht gelöscht, sondern deaktiviert, damit bestehende
 * Vermerke und Verkäufer-Angaben ihre Bezeichnung behalten.
 */
class EinstellungenController extends Controller
{
    public function reputation()
    {
        return view('einstellungen.reputation', [
            'sperreAb' => Einstellung::zahl('reputation_sperre_ab'),
            'warnungAb' => Einstellung::zahl('reputation_warnung_ab'),
            'zeitraumMonate' => Einstellung::zahl('reputation_zeitraum_monate'),
            'typen' => VermerkTyp::alle(),
        ]);
    }

    public function schwellenSpeichern(Request $request)
    {
        $daten = $request->validate([
            'sperre_ab' => 'required|integer|min:1|max:100',
            'warnung_ab' => 'required|integer|min:1|lte:sperre_ab',
            'zeitraum_monate' => 'required|integer|min:1|max:120',
        ], [
            'warnung_ab.lte' => 'Die Hinweis-Schwelle darf nicht über der Sperr-Schwelle liegen.',
        ]);

        Einstellung::setze('reputation_sperre_ab', $daten['sperre_ab']);
        Einstellung::setze('reputation_warnung_ab', $daten['warnung_ab']);
        Einstellung::setze('reputation_zeitraum_monate', $daten['zeitraum_monate']);

        AuditLogger::log('einstellungen.reputation', null, $daten);

        return redirect()->route('einstellungen.reputation')->with('success', 'Schwellen gespeichert.');
    }

    public function typAnlegen(Request $request)
    {
        $daten = $this->validiereTyp($request);

        $typ = VermerkTyp::create($daten + [
            'schluessel' => $this->eindeutigerSchluessel(VermerkTyp::class, $daten['label']),
            'aktiv' => true,
        ]);

        AuditLogger::log('einstellungen.vermerktyp.angelegt', $typ, $daten);

        return redirect()->route('einstellungen.reputation')->with('success', 'Vermerk-Art angelegt.');
    }

    public function typAktualisieren(Request $request, VermerkTyp $typ)
    {
        $daten = $this->validiereTyp($request);
        $daten['aktiv'] = $request->boolean('aktiv');
        $altePunkte = $typ->punkte;

        $typ->update($daten);

        // Optional: bestehende Vermerke dieser Art, die noch die bisherigen
        // Standardpunkte tragen, auf die neuen Punkte umstellen.
        $angepasst = 0;
        if ($request->boolean('bestehende_anpassen') && $altePunkte !== $typ->punkte) {
            $angepasst = VerkaeuferVermerk::where('typ', $typ->schluessel)
                ->where('punkte', $altePunkte)
                ->update(['punkte' => $typ->punkte]);
        }

        AuditLogger::log('einstellungen.vermerktyp.geaendert', $typ, $daten + [
            'alte_punkte' => $altePunkte,
            'vermerke_angepasst' => $angepasst,
        ]);

        return redirect()->route('einstellungen.reputation')->with('success', 'Vermerk-Art gespeichert.'.($angepasst ? " {$angepasst} bestehende Vermerke angepasst." : ''));
    }

    public function kategorien()
    {
        return view('einstellungen.kategorien', [
            'kategorien' => Angebotskategorie::alle(),
        ]);
    }

    public function kategorieAnlegen(Request $request)
    {
        $daten = $this->validiereKategorie($request);

        $kategorie = Angebotskategorie::create($daten + [
            'schluessel' => $this->eindeutigerSchluessel(Angebotskategorie::class, $daten['label']),
            'aktiv' => true,
        ]);

        AuditLogger::log('einstellungen.kategorie.angelegt', $kategorie, $daten);

        return redirect()->route('einstellungen.kategorien')->with('success', 'Kategorie angelegt.');
    }

    public function kategorieAktualisieren(Request $request, Angebotskategorie $kategorie)
    {
        $daten = $this->validiereKategorie($request);
        $daten['aktiv'] = $request->boolean('aktiv');

        $kategorie->update($daten);

        AuditLogger::log('einstellungen.kategorie.geaendert', $kategorie, $daten);

        return redirect()->route('einstellungen.kategorien')->with('success', 'Kategorie gespeichert.');
    }

    private function validiereTyp(Request $request): array
    {
        $daten = $request->validate([
            'label' => 'required|string|max:255',
            'punkte' => 'required|integer|min:0|max:10',
            'sortierung' => 'nullable|integer|min:0|max:10000',
        ]);

        return [
            'label' => $daten['label'],
            'punkte' => (int) $daten['punkte'],
            'sortierung' => (int) ($daten['sortierung'] ?? 0),
        ];
    }

    private function validiereKategorie(Request $request): array
    {
        $daten = $request->validate([
            'label' => 'required|string|max:255',
            'gruppe' => 'required|string|max:100',
            'groesse_von' => 'nullable|integer|min:0|max:999|required_with:groesse_bis',
            'groesse_bis' => 'nullable|integer|min:0|max:999|required_with:groesse_von|gte:groesse_von',
            'sortierung' => 'nullable|integer|min:0|max:10000',
        ], [
            'groesse_bis.gte' => '„Größe bis“ muss mindestens „Größe von“ sein.',
        ]);

        return [
            'label' => $daten['label'],
            'gruppe' => $daten['gruppe'],
            'groesse_von' => $daten['groesse_von'] ?? null,
            'groesse_bis' => $daten['groesse_bis'] ?? null,
            'sortierung' => $daten['sortierung'] ?? 0,
        ];
    }

    private function eindeutigerSchluessel(string $modelKlasse, string $label): string
    {
        $basis = Str::limit(Str::slug($label, '_'), 40, '') ?: 'eintrag';
        $schluessel = $basis;
        $i = 2;

        while ($modelKlasse::where('schluessel', $schluessel)->exists()) {
            $schluessel = $basis.'_'.$i++;
        }

        return $schluessel;
    }
}
