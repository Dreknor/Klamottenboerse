<?php

namespace App\Domain\Ablage;

use App\Models\Boerse;
use App\Models\Ordner;
use App\Models\Protokoll;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use SimpleXMLElement;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Einmalige Übernahme aus der Nextcloud per WebDAV – nur Bilder und Protokolle.
 * Bilder behalten ihre Ordnerstruktur. Protokolle aus .txt/.md werden zu Protokollen im Editor,
 * andere Formate (Word, PDF …) bleiben Dateien und bekommen einen Protokoll-Eintrag mit Link.
 * Die Börse wird über das Datum (aus dem Dateinamen, sonst Änderungsdatum) zugeordnet.
 * Wiederholbar: Bereits übernommene Dateien (gleicher Pfad) werden übersprungen.
 */
class NextcloudImport
{
    public const BILDER = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic'];

    public const TEXT = ['txt', 'md'];

    public const DOKUMENTE = ['pdf', 'doc', 'docx', 'odt', 'rtf'];

    /** @var array{bilder:int, protokolle:int, dateien:int, uebersprungen:int} */
    public array $zaehler = ['bilder' => 0, 'protokolle' => 0, 'dateien' => 0, 'uebersprungen' => 0];

    private string $basis;

    /** @param  string  $webdavUrl  z. B. https://cloud.example.org/remote.php/dav/files/benutzer */
    public function __construct(string $webdavUrl, private readonly string $benutzer, private readonly string $passwort)
    {
        $this->basis = rtrim($webdavUrl, '/');
    }

    public function bilder(string $pfad): void
    {
        $wurzel = Ordner::firstOrCreate(['parent_id' => null, 'name' => 'Bilder aus der Nextcloud'], ['herkunft' => $pfad]);

        foreach ($this->dateien($pfad) as $datei) {
            if (! in_array($datei['endung'], self::BILDER, true)) {
                continue;
            }
            $ordner = $this->ordnerFuer($wurzel, $datei['unterordner']);
            if ($this->speichern($ordner, $datei)) {
                $this->zaehler['bilder']++;
            }
        }
    }

    public function protokolle(string $pfad): void
    {
        $ablage = Ordner::firstOrCreate(['parent_id' => null, 'name' => 'Protokolle aus der Nextcloud'], ['herkunft' => $pfad]);

        foreach ($this->dateien($pfad) as $datei) {
            if (Protokoll::query()->where('herkunft', $datei['pfad'])->exists()) {
                $this->zaehler['uebersprungen']++;

                continue;
            }

            $datum = $this->datumAus($datei['name']) ?? $datei['geaendert'];
            $titel = Str::of($datei['name'])->beforeLast('.')->replace(['_', '-'], ' ')->squish()->toString();

            if (in_array($datei['endung'], self::TEXT, true)) {
                $inhalt = $this->herunterladen($datei['pfad']);
                $inhalt = mb_check_encoding($inhalt, 'UTF-8') ? $inhalt : mb_convert_encoding($inhalt, 'UTF-8', 'Windows-1252');
                Protokoll::create([
                    'titel' => Str::limit($titel, 180, ''), 'datum' => $datum, 'inhalt' => $inhalt,
                    'boerse_id' => $this->boerseZu($datum), 'herkunft' => $datei['pfad'],
                ]);
                $this->zaehler['protokolle']++;
            } elseif (in_array($datei['endung'], self::DOKUMENTE, true)) {
                $media = $this->speichern($this->ordnerFuer($ablage, $datei['unterordner']), $datei);
                if ($media) {
                    Protokoll::create([
                        'titel' => Str::limit($titel, 180, ''), 'datum' => $datum,
                        'inhalt' => "Übernommen aus der Nextcloud als Datei:\n\n[".$datei['name'].']('.route('admin.ablage.datei', $media).')',
                        'boerse_id' => $this->boerseZu($datum), 'herkunft' => $datei['pfad'],
                    ]);
                    $this->zaehler['dateien']++;
                }
            }
        }
    }

    /**
     * Alle Dateien unterhalb eines Pfades (rekursiv).
     *
     * @return Collection<int, array{pfad:string, name:string, endung:string, unterordner:list<string>, geaendert:CarbonImmutable}>
     */
    public function dateien(string $pfad): Collection
    {
        $start = trim($pfad, '/');
        $ergebnis = collect();
        $offen = [$start];

        while ($offen) {
            $ordner = array_shift($offen);
            foreach ($this->auflisten($ordner) as $eintrag) {
                if ($eintrag['ordner']) {
                    $offen[] = $eintrag['pfad'];

                    continue;
                }
                $relativ = ltrim(Str::after($eintrag['pfad'], $start), '/');
                $teile = explode('/', $relativ);
                $name = array_pop($teile);
                $ergebnis->push([
                    'pfad' => $eintrag['pfad'],
                    'name' => $name,
                    'endung' => Str::lower(pathinfo($name, PATHINFO_EXTENSION)),
                    'unterordner' => $teile,
                    'geaendert' => $eintrag['geaendert'],
                ]);
            }
        }

        return $ergebnis;
    }

    /** @return list<array{pfad:string, ordner:bool, geaendert:CarbonImmutable}> */
    private function auflisten(string $ordner): array
    {
        $antwort = $this->http()->withHeaders(['Depth' => '1', 'Content-Type' => 'application/xml'])
            ->send('PROPFIND', $this->url($ordner), ['body' => '<?xml version="1.0"?><d:propfind xmlns:d="DAV:"><d:prop><d:resourcetype/><d:getlastmodified/></d:prop></d:propfind>']);

        if ($antwort->status() !== 207) {
            throw new RuntimeException("Nextcloud-Ordner „{$ordner}“ nicht lesbar (HTTP {$antwort->status()}).");
        }

        $xml = new SimpleXMLElement($antwort->body());
        $xml->registerXPathNamespace('d', 'DAV:');
        $basisPfad = rtrim(parse_url($this->basis, PHP_URL_PATH) ?? '', '/');

        $eintraege = [];
        foreach ($xml->xpath('//d:response') as $response) {
            $response->registerXPathNamespace('d', 'DAV:');
            $href = rawurldecode((string) $response->xpath('d:href')[0]);
            $pfad = trim(Str::after($href, $basisPfad), '/');
            if ($pfad === trim($ordner, '/')) {
                continue; // der Ordner selbst
            }
            $geaendert = (string) ($response->xpath('.//d:getlastmodified')[0] ?? '');
            $eintraege[] = [
                'pfad' => $pfad,
                'ordner' => $response->xpath('.//d:resourcetype/d:collection') !== [],
                'geaendert' => $geaendert ? CarbonImmutable::parse($geaendert) : CarbonImmutable::now(),
            ];
        }

        return $eintraege;
    }

    private function speichern(Ordner $ordner, array $datei): ?Media
    {
        $vorhanden = $ordner->getMedia('dateien')->first(fn (Media $m) => $m->getCustomProperty('herkunft') === $datei['pfad']);
        if ($vorhanden) {
            $this->zaehler['uebersprungen']++;

            return null;
        }

        return $ordner->addMediaFromString($this->herunterladen($datei['pfad']))
            ->usingFileName($datei['name'])
            ->usingName(pathinfo($datei['name'], PATHINFO_FILENAME))
            ->withCustomProperties(['herkunft' => $datei['pfad'], 'hochgeladen_von' => 'Nextcloud-Übernahme'])
            ->toMediaCollection('dateien');
    }

    /** @param  list<string>  $teile */
    private function ordnerFuer(Ordner $wurzel, array $teile): Ordner
    {
        $ordner = $wurzel;
        foreach ($teile as $teil) {
            $ordner = Ordner::firstOrCreate(['parent_id' => $ordner->id, 'name' => Str::limit($teil, 120, '')]);
        }

        return $ordner;
    }

    private function herunterladen(string $pfad): string
    {
        $antwort = $this->http()->get($this->url($pfad));
        if (! $antwort->successful()) {
            throw new RuntimeException("Datei „{$pfad}“ konnte nicht geladen werden (HTTP {$antwort->status()}).");
        }

        return $antwort->body();
    }

    /** Datum aus Dateinamen wie "2025-03-12 Orga", "12.03.2025 Treffen" oder "Protokoll_20250312". */
    public function datumAus(string $name): ?CarbonImmutable
    {
        $muster = [
            '/(\d{4})-(\d{2})-(\d{2})/' => fn ($m) => [$m[1], $m[2], $m[3]],
            '/(\d{1,2})\.(\d{1,2})\.(\d{4})/' => fn ($m) => [$m[3], $m[2], $m[1]],
            '/(20\d{2})(\d{2})(\d{2})/' => fn ($m) => [$m[1], $m[2], $m[3]],
        ];
        foreach ($muster as $regex => $teile) {
            if (preg_match($regex, $name, $m)) {
                [$j, $mo, $t] = $teile($m);
                if (checkdate((int) $mo, (int) $t, (int) $j)) {
                    return CarbonImmutable::create((int) $j, (int) $mo, (int) $t);
                }
            }
        }

        return null;
    }

    /** Die nächste Börse am oder nach dem Datum (innerhalb von 6 Monaten). */
    private function boerseZu(CarbonImmutable $datum): ?int
    {
        return Boerse::query()
            ->whereBetween('verkaufstag', [$datum->toDateString(), $datum->addMonths(6)->toDateString()])
            ->orderBy('verkaufstag')->value('id');
    }

    private function url(string $pfad): string
    {
        $teile = array_map('rawurlencode', array_filter(explode('/', trim($pfad, '/')), 'strlen'));

        return $this->basis.'/'.implode('/', $teile);
    }

    private function http()
    {
        return Http::withBasicAuth($this->benutzer, $this->passwort)->timeout(60);
    }
}
