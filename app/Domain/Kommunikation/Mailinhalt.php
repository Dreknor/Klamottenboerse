<?php

namespace App\Domain\Kommunikation;

use Illuminate\Support\HtmlString;

/**
 * Inhalt eingehender Mails lesbar machen: Text aus HTML gewinnen (für Vorschau, Suche, Zitat)
 * und HTML abgeschottet anzeigen – ohne Skripte und ohne nachgeladene Bilder (Tracking).
 */
class Mailinhalt
{
    /** HTML in lesbaren Text umwandeln – mit Absätzen und Aufzählungen statt einem langen Block. */
    public static function textAusHtml(string $html): string
    {
        // Unsichtbares (Stile, Skripte, Kopf) komplett weg, sonst landet CSS im Text
        $html = preg_replace('#<(head|style|script|title)\b[^>]*>.*?</\1>#is', '', $html) ?? $html;
        $html = preg_replace('#<!--.*?-->#s', '', $html) ?? $html;

        $html = preg_replace('#<br\s*/?>#i', "\n", $html) ?? $html;
        $html = preg_replace('#<li\b[^>]*>#i', "\n- ", $html) ?? $html;
        $html = preg_replace('#</(p|div|h[1-6]|tr|table|ul|ol|blockquote)>#i', "\n\n", $html) ?? $html;
        $html = preg_replace('#</t[dh]>#i', "\t", $html) ?? $html;

        // Links: „Text (Adresse)“, damit die Adresse nicht verloren geht
        $html = preg_replace_callback('#<a\b[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)</a>#is', function ($t) {
            $text = trim(strip_tags($t[2]));
            $adresse = html_entity_decode($t[1]);

            return $text === '' || $text === $adresse || str_starts_with($adresse, 'mailto:') ? $text : "{$text} ({$adresse})";
        }, $html) ?? $html;

        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace("\u{00A0}", ' ', $text);
        $text = preg_replace('/[ \t]+\n/', "\n", $text) ?? $text;
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

        return trim($text);
    }

    /**
     * Vollständiges Dokument für ein abgeschottetes iframe (srcdoc + sandbox).
     * Die Content-Security-Policy verbietet Skripte und – solange nicht freigegeben – externe Bilder.
     */
    public static function htmlDokument(string $html, bool $bilderLaden = false): string
    {
        $bilder = $bilderLaden ? 'data: https: http:' : 'data:';
        $kopf = '<meta charset="utf-8">'
            .'<meta http-equiv="Content-Security-Policy" content="default-src \'none\'; img-src '.$bilder.'; style-src \'unsafe-inline\'; font-src data:">'
            .'<base target="_blank">'
            .'<style>body{margin:0;padding:4px;font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;font-size:15px;line-height:1.5;color:#292524;word-wrap:break-word;overflow-wrap:anywhere}'
            .'img{max-width:100%;height:auto}table{max-width:100%}blockquote{margin:0 0 0 .5em;padding-left:.75em;border-left:3px solid #d6d3d1;color:#57534e}a{color:#c2410c}</style>';

        // Skripte und Formulare vorsichtshalber schon hier entfernen (die Sandbox blockiert sie ohnehin)
        $html = preg_replace('#<(script|iframe|object|embed|form)\b[^>]*>.*?</\1>#is', '', $html) ?? $html;
        $html = preg_replace('#<(script|iframe|object|embed|meta|base|link)\b[^>]*/?>#i', '', $html) ?? $html;

        return '<!DOCTYPE html><html><head>'.$kopf.'</head><body>'.$html.'</body></html>';
    }

    /** Enthält die Mail Bilder aus dem Internet? Dann bieten wir „Bilder laden“ an. */
    public static function hatExterneBilder(string $html): bool
    {
        return (bool) preg_match('#<img\b[^>]*src=["\']?https?://#i', $html)
            || (bool) preg_match('#url\(\s*["\']?https?://#i', $html);
    }

    /**
     * Reiner Text für die Anzeige: Links klickbar, zitierte Zeilen („> …“) abgesetzt.
     * Alles wird escaped – es kann kein HTML aus der Mail durchrutschen.
     */
    public static function textAlsHtml(string $text): HtmlString
    {
        // Aufeinanderfolgende Zeilen gleicher Art (normal / Zitat) zu Blöcken zusammenfassen
        $bloecke = [];
        foreach (preg_split('/\R/', trim($text)) ?: [] as $zeile) {
            $zitat = str_starts_with(ltrim($zeile), '>');
            $inhalt = self::verlinken(e($zitat ? ltrim(substr(ltrim($zeile), 1)) : $zeile));
            if ($bloecke !== [] && end($bloecke)['zitat'] === $zitat) {
                $bloecke[array_key_last($bloecke)]['zeilen'][] = $inhalt;
            } else {
                $bloecke[] = ['zitat' => $zitat, 'zeilen' => [$inhalt]];
            }
        }

        return new HtmlString(collect($bloecke)->map(fn ($b) => $b['zitat']
            ? '<div class="mail-zitat">'.implode("\n", $b['zeilen']).'</div>'
            : '<div>'.implode("\n", $b['zeilen']).'</div>')->implode(''));
    }

    private static function verlinken(string $escaped): string
    {
        return preg_replace_callback(
            '#\bhttps?://[^\s<>"\']+#i',
            function ($t) {
                // Satzzeichen am Ende gehören nicht zur Adresse („siehe https://example.org.“)
                $adresse = rtrim($t[0], '.,;:!?)');

                return '<a href="'.$adresse.'" target="_blank" rel="noopener noreferrer">'.$adresse.'</a>'.substr($t[0], strlen($adresse));
            },
            $escaped,
        ) ?? $escaped;
    }
}
