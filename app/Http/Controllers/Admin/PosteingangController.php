<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Kommunikation\AntwortSenden;
use App\Domain\Kommunikation\ImapPostfach;
use App\Domain\Kommunikation\Platzhalter;
use App\Enums\NachrichtStatus;
use App\Http\Controllers\Controller;
use App\Models\Mailvorlage;
use App\Models\Nachricht;
use App\Models\Person;
use App\Models\Posteingang;
use App\Support\BoerseKontext;
use App\Support\Fehlermeldung;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class PosteingangController extends Controller
{
    public function index(Request $request): View
    {
        $ansicht = $request->query('ansicht', 'offen');

        return view('admin.posteingang.index', [
            'ansicht' => $ansicht,
            'konfiguriert' => ImapPostfach::istKonfiguriert(),
            'mails' => Posteingang::query()->with('person')
                ->when($ansicht === 'offen', fn ($q) => $q->offen())
                ->when($ansicht === 'erledigt', fn ($q) => $q->whereNotNull('erledigt_at')->where('spam', false))
                ->when($ansicht === 'spam', fn ($q) => $q->where('spam', true))
                ->when($request->query('suche'), fn ($q, $s) => $q->where(fn ($w) => $w->where('von_email', 'like', "%{$s}%")
                    ->orWhere('von_name', 'like', "%{$s}%")->orWhere('betreff', 'like', "%{$s}%")))
                ->latest('empfangen_at')->paginate(50)->withQueryString(),
        ]);
    }

    public function abrufen(ImapPostfach $postfach): RedirectResponse
    {
        try {
            $neu = $postfach->abrufen();
        } catch (Throwable $e) {
            report($e);

            return back()->with('fehler', 'Postfach konnte nicht abgerufen werden. '.(Fehlermeldung::bekannt($e) ?? 'Bitte die IMAP-Zugangsdaten in der Datei .env auf dem Server prüfen.'));
        }

        return back()->with('erfolg', $neu ? "{$neu} neue Mail(s) abgerufen." : 'Keine neuen Mails.');
    }

    public function show(Request $request, Posteingang $mail, BoerseKontext $kontext): View
    {
        if (! $mail->gelesen_at) {
            $mail->update(['gelesen_at' => now()]);
        }
        (new ImapPostfach)->htmlNachladen($mail);

        $boerse = $kontext->get();
        $antwortText = '';
        if ($request->filled('vorlage') && ($vorlage = Mailvorlage::find($request->integer('vorlage')))) {
            $antwortText = Platzhalter::ersetzen($vorlage->inhalt, Platzhalter::fuer($mail->person, $boerse));
        }

        [$vorname, $nachname] = $this->namensVorschlag($mail);

        return view('admin.posteingang.show', [
            'mail' => $mail->load('person'),
            'bilderLaden' => $request->boolean('bilder'),
            'boerse' => $boerse,
            'teilnahme' => $mail->person && $boerse ? $boerse->teilnahmen()->where('person_id', $mail->person_id)->first() : null,
            'vorlagen' => Mailvorlage::query()->orderBy('name')->pluck('name', 'id'),
            'antwortText' => $antwortText,
            'antworten' => $mail->person_id
                ? Nachricht::query()->where('person_id', $mail->person_id)->where('typ', 'antwort')->latest()->limit(5)->get()
                : collect(),
            'vorname' => $vorname,
            'nachname' => $nachname,
            'aehnliche' => $mail->person_id || mb_strlen($nachname) < 2 ? collect() : Person::query()
                ->where('nachname', 'like', '%'.$nachname.'%')->limit(5)->get(),
        ]);
    }

    public function antworten(Request $request, Posteingang $mail, AntwortSenden $senden): RedirectResponse
    {
        $daten = $request->validate(['text' => ['required', 'string', 'max:20000']]);
        $nachricht = $senden($mail, $daten['text'], $request->user());

        if ($nachricht->status !== NachrichtStatus::Versendet) {
            return back()->withInput()->with('fehler', 'Die Antwort konnte nicht versendet werden: '.$nachricht->fehler);
        }

        if ($request->boolean('erledigt')) {
            $mail->update(['erledigt_at' => now()]);

            return redirect()->route('admin.posteingang.index')->with('erfolg', 'Antwort gesendet, Mail erledigt.');
        }

        return back()->with('erfolg', 'Antwort gesendet.');
    }

    /** Person zuordnen – bestehende oder aus dem Absender neu anlegen. */
    public function zuordnen(Request $request, Posteingang $mail): RedirectResponse
    {
        if ($request->filled('person_id')) {
            $person = Person::findOrFail($request->integer('person_id'));
        } else {
            $daten = $request->validate([
                'vorname' => ['required', 'string', 'max:100'],
                'nachname' => ['required', 'string', 'max:100'],
            ]);
            $person = Person::query()->where('email', $mail->von_email)->first()
                ?? Person::create($daten + ['email' => $mail->von_email, 'email_verified_at' => now()]);
        }

        // Alle Mails dieses Absenders gleich mit zuordnen.
        Posteingang::query()->where('von_email', $mail->von_email)->whereNull('person_id')->update(['person_id' => $person->id]);

        return back()->with('erfolg', "Zugeordnet: {$person->name}.");
    }

    /** Alle offenen Mails (bzw. alle Treffer der aktuellen Suche) auf einmal erledigen – z. B. beim Start mit einem vollen Postfach. */
    public function alleErledigt(Request $request): RedirectResponse
    {
        $suche = $request->string('suche')->trim()->toString();

        $anzahl = Posteingang::query()->offen()
            ->when($suche !== '', fn ($q) => $q->where(fn ($w) => $w->where('von_email', 'like', "%{$suche}%")
                ->orWhere('von_name', 'like', "%{$suche}%")->orWhere('betreff', 'like', "%{$suche}%")))
            ->update(['erledigt_at' => now(), 'gelesen_at' => DB::raw('coalesce(gelesen_at, CURRENT_TIMESTAMP)')]);

        activity()->causedBy($request->user())->withProperties(['anzahl' => $anzahl, 'suche' => $suche])->log('Posteingang: alle als erledigt markiert');

        return redirect()->route('admin.posteingang.index')->with('erfolg', $anzahl === 1 ? '1 Mail als erledigt markiert.' : "{$anzahl} Mails als erledigt markiert.");
    }

    public function status(Request $request, Posteingang $mail): RedirectResponse
    {
        $aktion = $request->validate(['aktion' => ['required', 'in:erledigt,offen,spam']])['aktion'];

        $mail->update(match ($aktion) {
            'erledigt' => ['erledigt_at' => now()],
            'offen' => ['erledigt_at' => null, 'spam' => false],
            'spam' => ['spam' => true, 'erledigt_at' => now()],
        });

        if ($aktion === 'spam' && ImapPostfach::istKonfiguriert()) {
            try {
                (new ImapPostfach)->verschieben($mail, config('imap.ordner_spam'));
            } catch (Throwable $e) {
                report($e);
            }
        }

        return redirect()->route('admin.posteingang.index')->with('erfolg', match ($aktion) {
            'erledigt' => 'Als erledigt markiert.',
            'offen' => 'Wieder offen.',
            'spam' => 'Als Spam markiert.',
        });
    }

    /** @return array{0: string, 1: string} */
    private function namensVorschlag(Posteingang $mail): array
    {
        $name = trim((string) $mail->von_name);
        if ($name === '') {
            $name = Str::of($mail->von_email)->before('@')->replace(['.', '_', '-'], ' ')->title()->toString();
        }
        if (str_contains($name, ',')) {
            [$nach, $vor] = array_map('trim', explode(',', $name, 2));

            return [$vor, $nach];
        }
        $teile = preg_split('/\s+/', $name);
        $nachname = count($teile) > 1 ? array_pop($teile) : '';

        return [implode(' ', $teile), $nachname];
    }
}
