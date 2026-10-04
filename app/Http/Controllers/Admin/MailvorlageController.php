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

    public function edit(Request $request, Mailvorlage $mailvorlage, BoerseKontext $kontext): View
    {
        $beispiel = Platzhalter::fuer($request->user(), $kontext->get(), [
            'absage_link' => '#', 'angebot_link' => '#', 'angebot_bis' => 'Montag, 12. Oktober, 18:00 Uhr',
            'feedback_link' => '#', 'bestaetigen_link' => '#', 'aufgaben' => '- Beispielaufgabe', 'aufgaben_link' => '#',
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
        $mailvorlage->update($request->validate([
            'name' => ['required', 'string', 'max:120'],
            'betreff' => ['required', 'string', 'max:190'],
            'inhalt' => ['required', 'string', 'max:20000'],
        ]));

        return back()->with('erfolg', 'Vorlage gespeichert. Die Vorschau zeigt den neuen Text.');
    }
}
