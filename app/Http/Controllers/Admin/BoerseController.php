<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Boersen\Actions\BoerseAnlegen;
use App\Domain\Boersen\Actions\BoerseKopieren;
use App\Enums\BoerseStatus;
use App\Http\Controllers\Controller;
use App\Models\Boerse;
use App\Models\Ort;
use App\Support\BoerseKontext;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BoerseController extends Controller
{
    public function index(): View
    {
        return view('admin.boersen.index', [
            'boersen' => Boerse::query()->with('ort')->withCount(['teilnahmen as verkaeufer_count' => fn ($q) => $q->mitNummer()->where('ist_kinderhaus', false)])
                ->orderByDesc('verkaufstag')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.boersen.form', [
            'boerse' => Boerse::vorschlag(),
            'vorlagen' => Boerse::query()->orderByDesc('verkaufstag')->pluck('titel', 'id'),
            'orte' => Ort::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, BoerseAnlegen $anlegen, BoerseKopieren $kopieren, BoerseKontext $kontext): RedirectResponse
    {
        if ($request->filled('vorlage_id')) {
            $daten = $request->validate([
                'vorlage_id' => ['required', 'exists:boersen,id'],
                'verkaufstag' => ['required', 'date'],
                'titel' => ['required', 'string', 'max:120'],
            ]);
            $boerse = $kopieren(Boerse::findOrFail($daten['vorlage_id']), Carbon::parse($daten['verkaufstag']), $daten['titel']);
            $meldung = 'Börse angelegt. Termine, Schichten, Mailplan und Checkliste wurden aus der Vorlage übernommen – bitte kurz prüfen.';
        } else {
            $boerse = $anlegen($this->validiert($request));
            $meldung = 'Börse angelegt. Checkliste und Mailplan sind eingerichtet.';
        }

        $kontext->setzen($boerse);

        return redirect()->route('admin.boersen.edit', $boerse)->with('erfolg', $meldung);
    }

    public function edit(Boerse $boerse): View
    {
        return view('admin.boersen.form', [
            'boerse' => $boerse,
            'vorlagen' => collect(),
            'orte' => Ort::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Boerse $boerse): RedirectResponse
    {
        $boerse->update($this->validiert($request, $boerse));

        return back()->with('erfolg', 'Gespeichert.');
    }

    public function abschliessen(Boerse $boerse): RedirectResponse
    {
        $boerse->update(['status' => $boerse->status === BoerseStatus::Abgeschlossen ? BoerseStatus::Planung : BoerseStatus::Abgeschlossen]);
        activity()->performedOn($boerse)->log($boerse->status === BoerseStatus::Abgeschlossen ? 'Börse abgeschlossen' : 'Börse wieder geöffnet');

        return back()->with('erfolg', $boerse->status === BoerseStatus::Abgeschlossen
            ? 'Börse abgeschlossen. Automatische Mails für diese Börse sind gestoppt.'
            : 'Börse wieder geöffnet.');
    }

    /** @return array<string, mixed> */
    private function validiert(Request $request, ?Boerse $boerse = null): array
    {
        $daten = $request->validate([
            'titel' => ['required', 'string', 'max:120'],
            'verkaufstag' => ['required', 'date'],
            'ort_name' => ['nullable', 'string', 'max:120'],
            'ort_adresse' => ['nullable', 'string', 'max:200'],
            'anmeldung_kinderhaus_ab' => ['nullable', 'date'],
            'anmeldung_ab' => ['nullable', 'date'],
            'anlieferung_beginn' => ['nullable', 'date'],
            'anlieferung_ende' => ['nullable', 'date', 'after_or_equal:anlieferung_beginn'],
            'verkauf_beginn' => ['nullable', 'date'],
            'verkauf_ende' => ['nullable', 'date', 'after_or_equal:verkauf_beginn'],
            'abholung_beginn' => ['nullable', 'date'],
            'abholung_ende' => ['nullable', 'date', 'after_or_equal:abholung_beginn'],
            'nummer_von' => ['required', 'integer', 'min:1', 'max:998'],
            'nummer_bis' => ['required', 'integer', 'gt:nummer_von', 'max:999'],
            'block_toleranz' => ['required', 'integer', 'min:0', 'max:100'],
            'kapazitaet' => ['required', 'integer', 'min:1', 'max:999'],
            'kinderhaus_nummer' => ['required', 'integer', 'min:1', 'max:999'],
            'max_teile' => ['nullable', 'integer', 'min:1'],
            'max_kisten' => ['nullable', 'integer', 'min:1'],
            'provision_prozent' => ['required', 'numeric', 'min:0', 'max:100'],
            'rundung_cent' => ['required', 'integer', 'in:1,5,10,50,100'],
            'angebot_stunden' => ['required', 'integer', 'min:1', 'max:336'],
            'hinweise' => ['nullable', 'string', 'max:5000'],
            'live_erloes_freigegeben' => ['nullable', 'boolean'],
        ]);

        $ort = filled($daten['ort_name'] ?? null)
            ? Ort::firstOrCreate(['name' => $daten['ort_name']], ['adresse' => $daten['ort_adresse'] ?? null])
            : null;
        if ($ort && ($daten['ort_adresse'] ?? null) !== $ort->adresse) {
            $ort->update(['adresse' => $daten['ort_adresse']]);
        }

        $daten['ort_id'] = $ort?->id;
        $daten['provision_promille'] = (int) round($daten['provision_prozent'] * 10);
        $daten['live_erloes_freigegeben'] = $request->boolean('live_erloes_freigegeben');
        unset($daten['ort_name'], $daten['ort_adresse'], $daten['provision_prozent']);

        return $daten;
    }
}
