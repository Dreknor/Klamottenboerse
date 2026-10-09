<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Kommunikation\Platzhalter;
use App\Http\Controllers\Controller;
use App\Models\Mailvorlage;
use App\Support\BoerseKontext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MailvorlageController extends Controller
{
    public function index(): View
    {
        return view('admin.mail.vorlagen', ['vorlagen' => Mailvorlage::query()->orderBy('name')->get()]);
    }

    public function create(): View
    {
        return view('admin.mail.vorlage-neu', ['platzhalter' => Platzhalter::BESCHREIBUNG]);
    }

    public function store(Request $request): RedirectResponse
    {
        $daten = $this->validieren($request);

        // Eigene Vorlagen bekommen einen eindeutigen Schlüssel, damit sie nie mit Standardvorlagen kollidieren
        $basis = 'eigen_'.Str::slug($daten['name'], '_');
        $schluessel = $basis;
        for ($i = 2; Mailvorlage::query()->where('schluessel', $schluessel)->exists(); $i++) {
            $schluessel = $basis.'_'.$i;
        }

        $vorlage = Mailvorlage::create($daten + ['schluessel' => $schluessel, 'push' => $request->boolean('push')]);

        return redirect()->route('admin.mailvorlagen.edit', $vorlage)->with('erfolg', 'Vorlage angelegt. Sie steht jetzt im Mailplan und bei Antworten im Posteingang zur Auswahl.');
    }

    public function edit(Request $request, Mailvorlage $mailvorlage, BoerseKontext $kontext): View
    {
        $beispiel = Platzhalter::fuer($request->user(), $kontext->get(), [
            'absage_link' => '#', 'angebot_link' => '#', 'angebot_bis' => 'Montag, 12. Oktober, 18:00 Uhr',
            'feedback_link' => '#', 'bestaetigen_link' => '#', 'aufgaben' => '- Beispielaufgabe', 'aufgaben_link' => '#',
            'helfer' => 'Hanna Hilft', 'schicht' => 'Kasse, Samstag, 10. Oktober, 9:00–11:00 Uhr', 'besetzung' => '2 von 3', 'schichten_link' => '#',
            'schichten' => '- Kasse, Samstag, 10. Oktober, 9:00–11:00 Uhr ([absagen](#))',
        ]);

        return view('admin.mail.vorlage-bearbeiten', [
            'vorlage' => $mailvorlage,
            'platzhalter' => Platzhalter::BESCHREIBUNG,
            'vorschauBetreff' => Platzhalter::ersetzen($mailvorlage->betreff, $beispiel),
            'vorschauHtml' => Str::markdown(Platzhalter::ersetzen($mailvorlage->inhalt, $beispiel), ['html_input' => 'escape']),
        ]);
    }

    public function update(Request $request, Mailvorlage $mailvorlage): RedirectResponse
    {
        $mailvorlage->update($this->validieren($request));

        return back()->with('erfolg', 'Vorlage gespeichert. Die Vorschau zeigt den neuen Text.');
    }

    public function destroy(Mailvorlage $mailvorlage): RedirectResponse
    {
        if ($mailvorlage->istStandard()) {
            return back()->with('fehler', 'Standardvorlagen verschickt das System selbst – sie können nur geändert, nicht gelöscht werden.');
        }
        $mailvorlage->delete();

        return redirect()->route('admin.mailvorlagen.index')->with('erfolg', 'Vorlage gelöscht.');
    }

    /** @return array<string, mixed> */
    private function validieren(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'betreff' => ['required', 'string', 'max:190'],
            'inhalt' => ['required', 'string', 'max:20000'],
            'push' => ['nullable', 'boolean'],
        ]);
    }
}
