<?php

namespace App\Support;

/** Belehrung zum Unterschreiben bei der Kistenabgabe (Text je Börse, mit Platzhaltern). */
class Belehrung
{
    public const STANDARD = <<<'TXT'
Ich bin darüber informiert, dass die Elternvertretung des Evangelischen Kinderhauses der Friedenskirchgemeinde als Veranstalter der Klamottenbörse keine Haftung für abhanden gekommene Waren übernimmt, wenngleich sorgfältig darauf geachtet wird, dass dies nicht passiert.

Die nicht verkaufte Ware muss am Tag der Klamottenbörse, dem {datum}, zwischen {abholung_ab} und {abholung_bis} Uhr am Verkaufsort ({ort}) abgeholt werden.

**{provision} % des Verkaufserlöses spende ich an das Evangelische Kinderhaus der Friedenskirchgemeinde.**
TXT;

    /** V1 speicherte die Belehrung als HTML mit Platzhaltern wie DATUM oder ABHOLUNG_AB. */
    public static function ausV1Html(?string $html): ?string
    {
        if (blank($html)) {
            return null;
        }

        $text = preg_replace(['/<\/p>\s*/i', '/<br\s*\/?>/i', '/<\/?(strong|b)>/i'], ["\n\n", "\n", '**'], $html);
        $text = html_entity_decode(strip_tags((string) $text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace("\u{00A0}", ' ', $text);
        $text = strtr($text, [
            'ABHOLUNG_AB' => '{abholung_ab}', 'ABHOLUNG_BIS' => '{abholung_bis}',
            'ANLIEFERUNG_AB' => '{anlieferung_ab}', 'ANLIEFERUNG_BIS' => '{anlieferung_bis}',
            'DATUM' => '{datum}', 'ORT' => '{ort}',
        ]);
        $text = preg_replace(['/[ \t]+/', '/ *\n */', '/\n{3,}/', '/\*\*\s*\*\*/'], [' ', "\n", "\n\n", ''], $text);

        return trim((string) $text);
    }
}
