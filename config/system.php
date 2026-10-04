<?php

return [
    /*
    | Updates aus der Weboberfläche (Backend → System). Auf manchen Hostern heißt
    | PHP für die Kommandozeile anders (z. B. „php84“) oder Composer liegt als
    | composer.phar im Projekt („php composer.phar“).
    */
    'php' => env('UPDATE_PHP', 'php'),
    'composer' => env('UPDATE_COMPOSER', 'composer'),
    'git' => env('UPDATE_GIT', 'git'),
    'branch' => env('UPDATE_BRANCH'), // leer = aktueller Branch
];
