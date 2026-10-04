<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Kommunikation\Postausgang;
use App\Enums\Bezugsdatum;
use App\Enums\NachrichtStatus;
use App\Enums\Zielgruppe;
use App\Http\Controllers\Controller;
use App\Models\MailplanEintrag;
use App\Models\Mailvorlage;
use App\Models\Nachricht;
use App\Support\BoerseKontext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MailplanController extends Controller
{
    public function index(BoerseKontext $kontext): View
    {
        $boerse = $kontext->getOrFail();
        $plan = $boerse->mailplan()->with('vorlage')->get()->each->setRelation('boerse', $boerse)->sortBy(fn ($e) => $e->faelligAb()?->getTimestamp() ?? PHP_INT_MAX);

        return view('admin.mail.plan', [
            'boerse' => $boerse,
            'plan' => $plan,
            'anzahlJeEintrag' => Nachricht::query()->whereIn('mailplan_eintrag_id', $plan->pluck('id'))
                ->selectRaw('mailplan_eintrag_id, status, count(*) as anzahl')->groupBy('mailplan_eintrag_id', 'status')->get()
                ->groupBy('mailplan_eintrag_id'),
            'vorlagen' => Mailvorlage::query()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function store(Request $request, BoerseKontext $kontext): RedirectResponse
    {
        $daten = $request->validate([
            'mailvorlage_id' => ['required', 'exists:mailvorlagen,id'],
            'zielgruppe' => ['required', Rule::enum(Zielgruppe::class)],
            'bezugsdatum' => ['required', Rule::enum(Bezugsdatum::class)],
            'versatz_tage' => ['required', 'integer', 'min:-120', 'max:120'],
        ]);
        $kontext->getOrFail()->mailplan()->create($daten + ['aktiv' => true]);

        return back()->with('erfolg', 'Mail in den Plan aufgenommen.');
    }

    public function umschalten(MailplanEintrag $eintrag): RedirectResponse
    {
        $eintrag->update(['aktiv' => ! $eintrag->aktiv]);

        return back()->with('erfolg', $eintrag->aktiv ? 'Mail ist aktiv.' : 'Mail ist pausiert.');
    }

    public function destroy(MailplanEintrag $eintrag): RedirectResponse
    {
        if ($eintrag->eingeplant_at) {
            return back()->with('fehler', 'Diese Mail wurde schon verschickt und bleibt im Plan als Nachweis.');
        }
        $eintrag->delete();

        return back()->with('erfolg', 'Aus dem Plan entfernt.');
    }

    /** Postausgang: alle Mails mit Versandstatus. */
    public function postausgang(Request $request): View
    {
        return view('admin.mail.postausgang', [
            'nachrichten' => Nachricht::query()->with('person')
                ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
                ->when($request->query('suche'), fn ($q, $s) => $q->where(fn ($w) => $w->where('email', 'like', "%{$s}%")->orWhere('betreff', 'like', "%{$s}%")))
                ->latest()->paginate(50)->withQueryString(),
            'wartend' => Nachricht::query()->where('status', NachrichtStatus::Wartend)->count(),
            'kontingent' => Postausgang::restkontingent(),
        ]);
    }

    public function erneut(Nachricht $nachricht): RedirectResponse
    {
        $nachricht->update(['status' => NachrichtStatus::Wartend, 'fehler' => null]);

        return back()->with('erfolg', 'Mail wird erneut versendet.');
    }
}
