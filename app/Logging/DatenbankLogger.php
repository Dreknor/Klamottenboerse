<?php

namespace App\Logging;

use Monolog\Level;
use Monolog\Logger;

/** Log-Kanal „datenbank“ (config/logging.php). */
class DatenbankLogger
{
    /** @param array<string, mixed> $config */
    public function __invoke(array $config): Logger
    {
        return new Logger('datenbank', [new DatenbankHandler(Level::fromName($config['level'] ?? 'warning'))]);
    }
}
