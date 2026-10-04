<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Teilnahme\Nummernvergabe;
use App\Enums\NachrichtStatus;
use App\Enums\TeilnahmeStatus;
use App\Http\Controllers\Controller;
use App\Models\Boerse;
use App\Models\Nachricht;
use App\Models\Posteingang;
use App\Support\BoerseKontext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(BoerseKontext $kontext): View|RedirectResponse
    {
        $boerse = $kontext->get();
        if (! $boerse) {
            return redirect()->route('admin.boersen.create')->with('hinweis', 'Willkommen! Lege zuerst die erste Börse an.');
        }

        $status = $boerse->teilnahmen()->where('ist_kinderhaus', false)
            ->selectRaw('status, count(*) as anzahl')->groupBy('status')->pluck('anzahl', 'status');

        $aufgaben = $boerse->aufgaben()->with('zustaendig')
            ->orderByRaw('erledigt_at is not null')->orderBy('faellig_am')->orderBy('sortierung')->get();

        $schichten = $boerse->schichten()->withCount(['zusagen'])->get();

        return view('admin.dashboard', [
            'boerse' => $boerse,
            'phase' => $boerse->phase(),
            'status' => $status,
            'belegt' => $boerse->belegteNummern(),
            'warteliste' => (int) ($status[TeilnahmeStatus::Warteliste->value] ?? 0),
            'blockbelegung' => (new Nummernvergabe($boerse))->blockbelegung(),
            'aufgabenOffen' => $aufgaben->whereNull('erledigt_at'),
            'aufgabenErledigt' => $aufgaben->whereNotNull('erledigt_at')->count(),
            'schichtenSoll' => $schichten->sum('soll'),
            'schichtenBesetzt' => $schichten->sum(fn ($s) => min($s->soll, $s->zusagen_count)),
            'artikelErfasst' => $boerse->teilnahmen()->withCount('artikel')->get()->sum('artikel_count'),
            'umsatzCent' => (int) $boerse->bons()->whereNull('storniert_at')->sum('summe_cent'),
            'posteingangOffen' => Posteingang::query()->offen()->count(),
            'mailFehler' => Nachricht::query()->where('status', NachrichtStatus::Fehler)->where('boerse_id', $boerse->id)->count(),
            'mailWartend' => Nachricht::query()->where('status', NachrichtStatus::Wartend)->count(),
        ]);
    }

    public function wechseln(Request $request, BoerseKontext $kontext): RedirectResponse
    {
        $kontext->setzen(Boerse::query()->findOrFail($request->integer('boerse_id')));

        return back();
    }
}
