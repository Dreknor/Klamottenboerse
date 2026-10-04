<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Kasse\Stornieren;
use App\Http\Controllers\Controller;
use App\Models\Bon;
use App\Models\Bonposition;
use App\Support\BoerseKontext;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Alle Verkäufe einer Börse mit Storno-Möglichkeit. */
class VerkaufController extends Controller
{
    public function index(Request $request, BoerseKontext $kontext): View
    {
        $boerse = $kontext->getOrFail();
        $nummer = $request->integer('nummer') ?: null;

        return view('admin.verkaeufe.index', [
            'boerse' => $boerse,
            'bons' => $boerse->bons()
                ->with(['positionen.teilnahme:id,nummer', 'kassenschicht.kasse:id,name', 'kassenschicht.person:id,vorname,nachname'])
                ->when($nummer, fn ($q) => $q->whereHas('positionen.teilnahme', fn ($t) => $t->where('nummer', $nummer)))
                ->when($request->boolean('stornos'), fn ($q) => $q->where(fn ($w) => $w->whereNotNull('storniert_at')
                    ->orWhereHas('positionen', fn ($p) => $p->whereNotNull('storniert_at'))))
                ->latest('erstellt_am_geraet')
                ->paginate(50)->withQueryString(),
            'nummer' => $nummer,
        ]);
    }

    public function bonStornieren(Request $request, Bon $bon, Stornieren $stornieren): RedirectResponse
    {
        return $this->ausfuehren(fn () => $stornieren->bon($bon, $this->grund($request)), 'Bon storniert.');
    }

    public function positionStornieren(Request $request, Bonposition $position, Stornieren $stornieren): RedirectResponse
    {
        return $this->ausfuehren(fn () => $stornieren->position($position, $this->grund($request)), 'Position storniert.');
    }

    public function stornoZuruecknehmen(Bon $bon, Stornieren $stornieren): RedirectResponse
    {
        return $this->ausfuehren(fn () => $stornieren->zuruecknehmen($bon), 'Storno zurückgenommen.');
    }

    private function grund(Request $request): string
    {
        return $request->validate(['grund' => ['required', 'string', 'max:190']], ['grund.required' => 'Bitte einen Grund für den Storno angeben.'])['grund'];
    }

    private function ausfuehren(callable $aktion, string $meldung): RedirectResponse
    {
        try {
            $aktion();
        } catch (DomainException $e) {
            return back()->with('fehler', $e->getMessage());
        }

        return back()->with('erfolg', $meldung);
    }
}
