<?php

namespace App\Support;

use App\Models\Boerse;

/**
 * Die im Backend gewählte Börse. Jede Ansicht arbeitet auf genau einer Börse;
 * gewechselt wird über den Börsen-Wechsler oben im Menü.
 */
class BoerseKontext
{
    private ?Boerse $boerse = null;

    private bool $geladen = false;

    public function get(): ?Boerse
    {
        if (! $this->geladen) {
            $id = session('boerse_id');
            $this->boerse = ($id ? Boerse::query()->with('ort')->find($id) : null) ?? Boerse::aktuelle()?->load('ort');
            $this->geladen = true;
        }

        return $this->boerse;
    }

    public function getOrFail(): Boerse
    {
        return $this->get() ?? abort(redirect()->route('admin.boersen.create')->with('hinweis', 'Bitte lege zuerst eine Börse an.'));
    }

    public function setzen(Boerse $boerse): void
    {
        session(['boerse_id' => $boerse->id]);
        $this->boerse = $boerse;
        $this->geladen = true;
    }
}
