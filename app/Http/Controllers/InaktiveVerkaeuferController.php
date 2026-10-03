<?php

namespace App\Http\Controllers;

use App\Model\Interessenten;
use App\Model\Klamottenboerse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InaktiveVerkaeuferController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'monate' => ['sometimes', 'required', 'integer', 'min:1', 'max:600'],
            'reservierung' => ['sometimes', 'required', 'in:alle,ja,nein'],
        ]);
        $monate = (int) ($validated['monate'] ?? 24);
        $reservierung = $validated['reservierung'] ?? 'alle';
        $stichtag = today()->subMonthsNoOverflow($monate);
        $aktuelleBoerse = Klamottenboerse::query()->latest('id')->first();
        $aktuelleBoerseId = $aktuelleBoerse ? $aktuelleBoerse->id : null;

        $letzteTeilnahmen = DB::table('vknummern')
            ->join('klamottenboerse', 'klamottenboerse.id', '=', 'vknummern.klamottenboersen_id')
            ->whereNull('klamottenboerse.deleted_at')
            ->whereNotNull('vknummern.vergeben_an')
            ->whereDate('klamottenboerse.datum', '<=', today()->toDateString())
            ->select('vknummern.vergeben_an')
            ->selectRaw('MAX(klamottenboerse.datum) as letzte_teilnahme')
            ->groupBy('vknummern.vergeben_an');

        $aktuelleReservierungen = function ($query) use ($aktuelleBoerseId) {
            $query->where('klamottenboersen_id', $aktuelleBoerseId);
        };

        $query = Interessenten::query()
            ->leftJoinSub($letzteTeilnahmen, 'teilnahmen', function ($join) {
                $join->on('interessenten.id', '=', 'teilnahmen.vergeben_an');
            })
            ->select('interessenten.*', 'teilnahmen.letzte_teilnahme')
            ->where(function ($query) use ($stichtag) {
                $query->whereDate('teilnahmen.letzte_teilnahme', '<=', $stichtag->toDateString())
                    ->orWhere(function ($query) use ($stichtag) {
                        $query->whereNull('teilnahmen.letzte_teilnahme')
                            ->whereDate('interessenten.created_at', '<=', $stichtag->toDateString());
                    });
            })
            ->with(['reservierteNummern' => function ($query) use ($aktuelleReservierungen) {
                $aktuelleReservierungen($query);
                $query->orderBy('vknummer');
            }]);

        if ($reservierung === 'ja') {
            $query->whereHas('reservierteNummern', $aktuelleReservierungen);
        } elseif ($reservierung === 'nein') {
            $query->whereDoesntHave('reservierteNummern', $aktuelleReservierungen);
        }

        $verkaeufer = $query
            ->orderBy('teilnahmen.letzte_teilnahme')
            ->orderBy('interessenten.nachname')
            ->orderBy('interessenten.vorname')
            ->orderBy('interessenten.id')
            ->paginate(50)
            ->withQueryString();

        foreach ($verkaeufer as $person) {
            if ($person->letzte_teilnahme !== null) {
                $person->letzte_teilnahme = Carbon::parse($person->letzte_teilnahme);
            }
        }

        return view('interessenten.inaktive-verkaeufer', compact(
            'verkaeufer', 'monate', 'reservierung', 'stichtag', 'aktuelleBoerse'
        ));
    }
}
