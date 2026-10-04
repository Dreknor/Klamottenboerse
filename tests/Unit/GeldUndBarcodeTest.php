<?php

use App\Domain\Kommunikation\Platzhalter;
use App\Support\Barcode;
use App\Support\Geld;

it('liest Beträge in Cent ein', function (string $eingabe, int $cent) {
    expect(Geld::parse($eingabe))->toBe($cent);
})->with([
    ['4,50', 450],
    ['4.50', 450],
    ['4,5', 450],
    ['4', 400],
    ['1.234,56', 123456],
    ['0,1', 10],
]);

it('formatiert Cent als Euro', function () {
    expect(Geld::format(123456))->toBe('1.234,56 €')
        ->and(Geld::format(5))->toBe('0,05 €');
});

it('rechnet Promille kaufmännisch gerundet', function () {
    expect(Geld::promille(1000, 250))->toBe(250)
        ->and(Geld::promille(1, 250))->toBe(0)   // 0,25 Cent → 0
        ->and(Geld::promille(2, 250))->toBe(1)   // 0,5 Cent → 1
        ->and(Geld::promille(1234, 250))->toBe(309);
});

it('zerlegt Beträge in möglichst wenige Scheine und Münzen', function () {
    expect(Geld::stueckelung(8770))->toBe([5000 => 1, 2000 => 1, 1000 => 1, 500 => 1, 200 => 1, 50 => 1, 20 => 1]);
});

it('kodiert und liest den Etiketten-Barcode', function () {
    $code = Barcode::kodieren(215, 7, 450);

    expect($code)->toBe('21500700450')
        ->and(Barcode::lesen($code))->toBe(['nummer' => 215, 'artikel' => 7, 'preis_cent' => 450])
        ->and(Barcode::lesen('abc'))->toBeNull()
        ->and(Barcode::lesen('2150070045'))->toBeNull();
});

it('lässt Zeilen mit nur leeren Platzhaltern weg', function () {
    $text = "Hallo {vorname},\n- Verkauf: {verkauf}\n- Ort: {ort}\n{unbekannt}";

    expect(Platzhalter::ersetzen($text, ['vorname' => 'Mia', 'verkauf' => '', 'ort' => 'Saal']))
        ->toBe("Hallo Mia,\n- Ort: Saal\n{unbekannt}");
});
