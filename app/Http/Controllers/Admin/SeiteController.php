<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seite;
use App\Support\Einstellungen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Bearbeitung der Website-Seiten (Impressum, Datenschutz) durch das Orga-Team. */
class SeiteController extends Controller
{
    public function index(): View
    {
        return view('admin.seiten.index', [
            'seiten' => Seite::query()->with('bearbeitetVon')->orderBy('titel')->get(),
            'unvollstaendig' => Seite::betreiberUnvollstaendig(),
        ]);
    }

    public function edit(Seite $seite): View
    {
        return view('admin.seiten.edit', [
            'seite' => $seite,
            'platzhalter' => Einstellungen::BETREIBER,
            'unvollstaendig' => Seite::betreiberUnvollstaendig(),
        ]);
    }

    public function update(Request $request, Seite $seite): RedirectResponse
    {
        $seite->update($request->validate([
            'titel' => ['required', 'string', 'max:120'],
            'inhalt' => ['required', 'string', 'max:100000'],
        ]) + ['bearbeitet_von' => $request->user()->id]);

        return back()->with('erfolg', 'Seite gespeichert und veröffentlicht.');
    }
}
