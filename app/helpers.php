<?php
function replaceInText($text, App\Model\Interessenten $interessenten, $Klamottenboerse = null): string
{
    if (is_null($Klamottenboerse)) {
        $KlamottenboersenRepository = new KlamottenboersenRepository();
        $Klamottenboerse = $KlamottenboersenRepository->aktuelleKlamottenboerse();
    }

    //Anrede
    if ($interessenten->anrede == 'Herr') {
        $Anrede = 'Sehr geehrter Herr';
        $Liebe = 'Lieber';
    } elseif ($interessenten->anrede == 'Frau') {
        $Anrede = 'Sehr geehrte Frau';
        $Liebe = 'Liebe';
    } elseif ($interessenten->anrede == 'Familie') {
        $Anrede = 'Sehr geehrte Familie';
        $Liebe = 'Liebe Familie';
    } else {
        $Anrede = '';
        $Liebe = '';
    }

    if (isset(auth()->user()->name)) {
        $absender = auth()->user()->name;
    } else {
        $absender = 'Das Team der Klamottenboerse';
    }

    $ReplaceStrings = [
        'VORNAME' => $interessenten->vorname ?? '',
        'NACHNAME'=> $interessenten->nachname ?? '',
        'ANREDE'=> $Anrede,
        'LIEBE'=> $Liebe,
        'ABSENDER' => $absender,
        'EMAIL'=> $interessenten->mail ?? '',
        'VKNUMMER'=> $interessenten->vknummern_vergeben->vknummer ?? '',
        'DATUM' => $Klamottenboerse->datum->format('d.m.Y'),
        'ANMELDUNG' => $Klamottenboerse->anmeldung->format('d.m.Y'),
        'ANNAHME' => $Klamottenboerse->datum->subDay()->format('d.m.Y'),
        'ANLIEFERUNG_AB'=> $Klamottenboerse->anlieferung_von,
        'ANLIEFERUNG_BIS' => $Klamottenboerse->anlieferung_bis,
        'ABHOLUNG_AB' => $Klamottenboerse->abholung_von,
        'ABHOLUNG_BIS' => $Klamottenboerse->abholung_bis,
        'MAXTEILE' => $Klamottenboerse->maxTeile,
    ];

    $replaced_text = str_replace(array_keys($ReplaceStrings), $ReplaceStrings, $text);

    return $replaced_text;
}
