<?php

return [
    /*
    | Demo-Modus für eine eigene Test-Installation (z. B. demo.klamottenboerse.de):
    | erfundene Daten, Anmeldung per Klick, keine Mails/Push nach außen,
    | jede Nacht automatisch zurückgesetzt. NIE in der echten Installation einschalten!
    */
    'aktiv' => (bool) env('DEMO_MODUS', false),

    // Uhrzeit des nächtlichen Zurücksetzens
    'zuruecksetzen_um' => env('DEMO_ZURUECKSETZEN_UM', '03:15'),
];
