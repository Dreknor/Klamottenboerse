<?php

namespace App\Http\Controllers;

use App\Model\Interessenten;
use App\Model\Klamottenboerse;
use App\Model\MailLog;
use App\Jobs\SendInaktivLoeschungMailJob;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InaktiveVerkaeuferController extends Controller
{
    private const SORT_COLUMNS = [
        'name' => 'interessenten.nachname',
        'mail' => 'interessenten.mail',
        'telefon' => 'interessenten.telefon',
        'letzte_teilnahme' => 'teilnahmen.letzte_teilnahme',
        'created_at' => 'interessenten.created_at',
        'nummern' => 'reservierte_nummern_min_vknummer',
    ];

    public function index(Request $request)
    {
        $validated = $this->filters($request);
        $monate = (int) ($validated['monate'] ?? 24);
        $reservierung = $validated['reservierung'] ?? 'alle';
        $stichtag = today()->subMonthsNoOverflow($monate);
        $aktuelleBoerse = Klamottenboerse::query()->latest('id')->first();
        $sort = $validated['sort'] ?? 'letzte_teilnahme';
        $richtung = $validated['richtung'] ?? 'asc';
        $verkaeufer = $this->query($validated)
            ->orderBy(self::SORT_COLUMNS[$sort], $richtung)
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
            'verkaeufer', 'monate', 'reservierung', 'stichtag', 'aktuelleBoerse', 'sort', 'richtung'
        ));
    }

    private function filters(Request $request): array
    {
        return $request->validate([
            'monate' => ['sometimes', 'required', 'integer', 'min:1', 'max:600'],
            'reservierung' => ['sometimes', 'required', 'in:alle,ja,nein'],
            'sort' => ['sometimes', 'required', Rule::in(array_keys(self::SORT_COLUMNS))],
            'richtung' => ['sometimes', 'required', 'in:asc,desc'],
            'suche' => ['nullable', 'string', 'max:200'],
            'spalten' => ['sometimes', 'array:name,mail,telefon,letzte_teilnahme,created_at,nummern'],
            'spalten.*' => ['nullable', 'string', 'max:200'],
        ]);
    }

    private function query(array $validated)
    {
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
            }])
            ->withMin(['reservierteNummern' => $aktuelleReservierungen], 'vknummer');

        if ($reservierung === 'ja') {
            $query->whereHas('reservierteNummern', $aktuelleReservierungen);
        } elseif ($reservierung === 'nein') {
            $query->whereDoesntHave('reservierteNummern', $aktuelleReservierungen);
        }

        $searchColumn = function ($query, $column, $term) use ($aktuelleReservierungen) {
            $like = '%'.$term.'%';
            if ($column === 'name') {
                $query->where(function ($query) use ($like) {
                    $query->where('interessenten.nachname', 'like', $like)
                        ->orWhere('interessenten.vorname', 'like', $like);
                });
            } elseif ($column === 'telefon') {
                $query->where(function ($query) use ($like) {
                    $query->where('interessenten.telefon', 'like', $like)
                        ->orWhere('interessenten.handy', 'like', $like);
                });
            } elseif ($column === 'nummern' && mb_strtolower($term) === 'keine') {
                $query->whereDoesntHave('reservierteNummern', $aktuelleReservierungen);
            } elseif ($column === 'nummern') {
                $query->whereHas('reservierteNummern', function ($query) use ($aktuelleReservierungen, $like) {
                    $aktuelleReservierungen($query);
                    $query->where('vknummer', 'like', $like);
                });
            } elseif ($column === 'letzte_teilnahme' && mb_strtolower($term) === 'nie') {
                $query->whereNull('teilnahmen.letzte_teilnahme');
            } else {
                $query->where(self::SORT_COLUMNS[$column], 'like', $like);
            }
        };
        foreach ($validated['spalten'] ?? [] as $column => $term) {
            if ($term !== null && trim($term) !== '') {
                $searchColumn($query, $column, trim($term));
            }
        }
        if (isset($validated['suche']) && trim($validated['suche']) !== '') {
            $query->where(function ($query) use ($searchColumn, $validated) {
                foreach (array_keys(self::SORT_COLUMNS) as $column) {
                    $query->orWhere(function ($query) use ($searchColumn, $column, $validated) {
                        $searchColumn($query, $column, trim($validated['suche']));
                    });
                }
            });
        }

        return $query;
    }

    public function destroy(Request $request)
    {
        $filters = $this->filters($request);
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:50'],
            'ids.*' => ['required', 'integer', 'distinct'],
            'bestaetigung' => ['accepted'],
        ]);

        DB::transaction(function () use ($filters, $validated) {
            $personen = $this->query($filters)->whereIn('interessenten.id', $validated['ids'])
                ->lockForUpdate()->get();
            if ($personen->count() !== count($validated['ids'])) {
                throw ValidationException::withMessages([
                    'ids' => 'Die Auswahl ist nicht mehr aktuell. Bitte die Liste neu laden und erneut auswählen.',
                ]);
            }
            foreach ($personen as $person) {
                if (Validator::make(['mail' => $person->mail], ['mail' => 'required|email'])->fails()) {
                    throw ValidationException::withMessages([
                        'ids' => 'Keine Löschung: '.$person->vorname.' '.$person->nachname.' hat keine gültige E-Mail-Adresse.',
                    ]);
                }
            }
            foreach ($personen as $person) {
                $log = MailLog::create([
                    'interessent_id' => $person->id,
                    'typ' => 'inaktivLoeschung',
                    'email' => $person->mail,
                    'betreff' => 'Dein Eintrag bei der Klamottenbörse',
                    'status' => MailLog::STATUS_QUEUED,
                ]);
                SendInaktivLoeschungMailJob::dispatch($log->id, $person->vorname, $person->nachname)->afterCommit();
                $person->delete();
                AuditLogger::log('interessent.inaktiv_massenloeschung', $person, [
                    'mail_log_id' => $log->id,
                ]);
            }
        });

        return redirect()->route('interessenten.inaktive-verkaeufer', $filters)
            ->with('Meldung', count($validated['ids']).' Einträge gelöscht. Die Benachrichtigungsmails sind zum Versand vorgemerkt.')
            ->with('type', 'success');
    }
}
