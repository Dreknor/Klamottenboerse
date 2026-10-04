<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Kommunikation\ImapPostfach;
use App\Http\Controllers\Controller;
use App\Support\Einstellungen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EinstellungenController extends Controller
{
    public function edit(Request $request): View
    {
        abort_unless($request->user()->hasRole('admin'), 403);

        return view('admin.einstellungen', [
            'werte' => Einstellungen::alle(),
            'imap' => ImapPostfach::istKonfiguriert() ? config('imap.username').' @ '.config('imap.host') : null,
            'mailer' => config('mail.default'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasRole('admin'), 403);

        $daten = $request->validate([
            'vereinsname' => ['required', 'string', 'max:190'],
            'empfaenger_spende' => ['required', 'string', 'max:190'],
            'mail_max_pro_stunde' => ['required', 'integer', 'min:1', 'max:10000'],
            'erinnerung_aufgaben_tage' => ['required', 'integer', 'min:0', 'max:30'],
        ] + collect(Einstellungen::BETREIBER)->mapWithKeys(fn ($_, $schluessel) => [$schluessel => ['nullable', 'string', 'max:500']])->all());

        $zahlen = ['mail_max_pro_stunde', 'erinnerung_aufgaben_tage'];
        foreach ($daten as $schluessel => $wert) {
            Einstellungen::set($schluessel, in_array($schluessel, $zahlen, true) ? (int) $wert : trim((string) $wert));
        }

        return back()->with('erfolg', 'Einstellungen gespeichert.');
    }
}
