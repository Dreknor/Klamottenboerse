<?php

namespace App\Domain\Push;

use App\Models\Person;
use App\Models\PushAbo;
use App\Models\PushNachricht;
use App\Support\Einstellungen;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\VAPID;
use Minishlink\WebPush\WebPush;
use RuntimeException;
use Throwable;

/**
 * Push-Nachrichten aufs Handy (Web Push, ohne App). Wer sie im Portal oder unter „Mein Konto“
 * einschaltet, bekommt Erinnerungen zusätzlich zur Mail. Versand gesammelt über push:versenden.
 */
class Push
{
    /** Öffentlicher Schlüssel für den Browser. Wird beim ersten Mal automatisch erzeugt. */
    public static function oeffentlicherSchluessel(): string
    {
        return self::schluessel()['publicKey'];
    }

    /** Merkt eine Push-Nachricht vor – nur, wenn die Person mindestens ein Gerät angemeldet hat. */
    public static function einplanen(Person $person, string $titel, string $text, ?string $url = null): ?PushNachricht
    {
        if (! PushAbo::query()->where('person_id', $person->id)->exists()) {
            return null;
        }

        return PushNachricht::create([
            'person_id' => $person->id,
            'titel' => Str::limit($titel, 120),
            'text' => Str::limit(self::klartext($text), 240),
            'url' => $url,
        ]);
    }

    /** Sendet wartende Push-Nachrichten. Abgelaufene Geräte werden entfernt. */
    public static function versenden(int $grenze = 500): int
    {
        $nachrichten = PushNachricht::query()->where('status', 'wartend')->orderBy('id')->limit($grenze)->get();
        if ($nachrichten->isEmpty()) {
            return 0;
        }

        $schluessel = self::schluessel();
        $webPush = new WebPush(['VAPID' => [
            'subject' => 'mailto:'.(config('mail.from.address') ?: 'info@example.org'),
            'publicKey' => $schluessel['publicKey'],
            'privateKey' => $schluessel['privateKey'],
        ]], ['TTL' => 86400]);

        $abos = PushAbo::query()->whereIn('person_id', $nachrichten->pluck('person_id'))->get()->groupBy('person_id');
        $zuordnung = [];

        foreach ($nachrichten as $nachricht) {
            foreach ($abos->get($nachricht->person_id, collect()) as $abo) {
                $webPush->queueNotification(
                    Subscription::create(['endpoint' => $abo->endpoint, 'keys' => ['p256dh' => $abo->p256dh, 'auth' => $abo->auth]]),
                    json_encode(['titel' => $nachricht->titel, 'text' => $nachricht->text, 'url' => $nachricht->url ?? url('/')], JSON_UNESCAPED_UNICODE),
                );
                $zuordnung[] = $abo;
            }
        }

        $erfolgreich = 0;
        foreach ($webPush->flush() as $i => $bericht) {
            $abo = collect($zuordnung)->firstWhere('endpoint', $bericht->getEndpoint()) ?? $zuordnung[$i] ?? null;
            if ($bericht->isSuccess()) {
                $erfolgreich++;
                $abo?->update(['zuletzt_genutzt_at' => now()]);
            } elseif ($bericht->isSubscriptionExpired()) {
                $abo?->delete(); // Gerät hat Push abbestellt oder App gelöscht
            } else {
                Log::warning('Push fehlgeschlagen: '.$bericht->getReason());
            }
        }

        PushNachricht::query()->whereIn('id', $nachrichten->pluck('id'))->update(['status' => 'versendet', 'versendet_at' => now()]);

        return $erfolgreich;
    }

    /** @return array{publicKey: string, privateKey: string} */
    private static function schluessel(): array
    {
        $oeffentlich = Einstellungen::get('push_vapid_public');
        $privat = Einstellungen::get('push_vapid_private');

        if (! $oeffentlich || ! $privat) {
            $neu = self::neueSchluessel();
            Einstellungen::set('push_vapid_public', $oeffentlich = $neu['publicKey']);
            Einstellungen::set('push_vapid_private', $privat = $neu['privateKey']);
        }

        return ['publicKey' => $oeffentlich, 'privateKey' => $privat];
    }

    /**
     * VAPID-Schlüsselpaar (P-256). Unter Windows findet OpenSSL oft keine Konfiguration –
     * dann mit der mitgelieferten Minimal-Konfiguration selbst erzeugen.
     *
     * @return array{publicKey: string, privateKey: string}
     */
    private static function neueSchluessel(): array
    {
        try {
            return VAPID::createVapidKeys();
        } catch (Throwable) {
            $schluessel = openssl_pkey_new(['ec' => ['curve_name' => 'prime256v1'], 'config' => resource_path('openssl.cnf')]);
            if (! $schluessel) {
                throw new RuntimeException('Push-Schlüssel konnten nicht erzeugt werden (OpenSSL).');
            }
            $ec = openssl_pkey_get_details($schluessel)['ec'];
            $b64 = fn (string $bytes) => rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
            $auf32 = fn (string $bytes) => str_pad($bytes, 32, "\0", STR_PAD_LEFT);

            return [
                'publicKey' => $b64("\x04".$auf32($ec['x']).$auf32($ec['y'])),
                'privateKey' => $b64($auf32($ec['d'])),
            ];
        }
    }

    /** Markdown-Links und Formatierung aus Mailtexten entfernen. */
    public static function klartext(string $text): string
    {
        $text = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $text);
        $text = str_replace(['**', '__', '#'], '', (string) $text);

        return trim((string) preg_replace('/\s+/', ' ', $text));
    }
}
