#!/usr/bin/env bash
#
# Klamottenbörse V2 – Neuinstallation
#
# Klont das Repository, fragt die Angaben für die .env ab und richtet eine
# frische V2-Installation ein (Composer, Datenbank, Grunddaten, Admin-Zugang).
# Node wird nicht gebraucht: die gebauten Assets (public/build) liegen im Repository.
#
# Aufruf:
#   bash install.sh [ZIELVERZEICHNIS]
#   curl -fsSL https://raw.githubusercontent.com/Dreknor/Klamottenboerse/v2/install.sh | bash -s -- [ZIELVERZEICHNIS]
#
# Optional über Umgebungsvariablen:
#   REPO_URL   (Standard: https://github.com/Dreknor/Klamottenboerse.git)
#   BRANCH     (Standard: v2)
#   PHP_BIN    (Standard: wird gesucht, z. B. php84, php8.4, php)
#   EINGABE    (Standard: /dev/tty – z. B. eine Datei mit Antworten für automatisierte Installationen)
#
# Das Skript installiert nur in ein leeres Verzeichnis und nur in eine leere
# Datenbank. Für Updates einer bestehenden Installation: Backend → System & Fehler.

set -euo pipefail

REPO_URL="${REPO_URL:-https://github.com/Dreknor/Klamottenboerse.git}"
BRANCH="${BRANCH:-v2}"
PHP_MIN="8.3.0"

# --- Ausgabe & Eingabe ------------------------------------------------------

if [ -t 1 ]; then
    FETT=$'\e[1m'; ROT=$'\e[31m'; GRUEN=$'\e[32m'; GELB=$'\e[33m'; NORMAL=$'\e[0m'
else
    FETT=''; ROT=''; GRUEN=''; GELB=''; NORMAL=''
fi

schritt() { printf '\n%s==> %s%s\n' "$FETT" "$1" "$NORMAL"; }
info()    { printf '    %s\n' "$1"; }
ok()      { printf '    %s✓ %s%s\n' "$GRUEN" "$1" "$NORMAL"; }
warnung() { printf '    %s! %s%s\n' "$GELB" "$1" "$NORMAL"; }
abbruch() { printf '\n%sFehler: %s%s\n' "$ROT" "$1" "$NORMAL" >&2; exit 1; }

# Eingaben immer vom Terminal lesen – funktioniert auch bei "curl … | bash".
EINGABE="${EINGABE:-/dev/tty}"
[ -r "$EINGABE" ] || abbruch "Kein Terminal verfügbar. Bitte das Skript interaktiv ausführen."
exec 3<"$EINGABE"

# frage VAR "Text" [Vorgabe]
frage() {
    local var="$1" text="$2" vorgabe="${3-}" antwort
    if [ -n "$vorgabe" ]; then
        read -r -p "    $text [$vorgabe]: " antwort <&3
        antwort="${antwort:-$vorgabe}"
    else
        read -r -p "    $text: " antwort <&3
    fi
    printf -v "$var" '%s' "$antwort"
}

# frage_pflicht VAR "Text" [Vorgabe] – wiederholt, bis etwas eingegeben wurde
frage_pflicht() {
    local var="$1"
    while :; do
        frage "$@"
        [ -n "${!var}" ] && return
        warnung "Bitte einen Wert eingeben."
    done
}

# frage_geheim VAR "Text" – ohne Anzeige, leere Eingabe erlaubt
frage_geheim() {
    local var="$1" text="$2" antwort
    read -r -s -p "    $text: " antwort <&3
    printf '\n'
    printf -v "$var" '%s' "$antwort"
}

# ja_nein "Frage" [j|n] – Rückgabewert 0 bei ja
ja_nein() {
    local text="$1" vorgabe="${2:-n}" antwort hinweis
    [ "$vorgabe" = "j" ] && hinweis="J/n" || hinweis="j/N"
    read -r -p "    $text [$hinweis]: " antwort <&3
    antwort="${antwort:-$vorgabe}"
    case "$antwort" in [jJyY]*) return 0 ;; *) return 1 ;; esac
}

# --- Voraussetzungen --------------------------------------------------------

printf '%sKlamottenbörse V2 – Neuinstallation%s\n' "$FETT" "$NORMAL"
info "Repository: $REPO_URL (Branch $BRANCH)"

schritt "Voraussetzungen prüfen"

command -v git >/dev/null 2>&1 || abbruch "git ist nicht installiert."
ok "git gefunden"

php_version_ok() {
    "$1" -r "exit(version_compare(PHP_VERSION, '$PHP_MIN', '>=') ? 0 : 1);" >/dev/null 2>&1
}

if [ -z "${PHP_BIN:-}" ]; then
    for kandidat in php85 php8.5 php84 php8.4 php83 php8.3 php; do
        if command -v "$kandidat" >/dev/null 2>&1 && php_version_ok "$kandidat"; then
            PHP_BIN="$kandidat"
            break
        fi
    done
fi
[ -n "${PHP_BIN:-}" ] || abbruch "Kein PHP >= $PHP_MIN gefunden. Mit PHP_BIN=/pfad/zu/php erneut aufrufen."
php_version_ok "$PHP_BIN" || abbruch "$PHP_BIN ist älter als PHP $PHP_MIN."
ok "PHP: $PHP_BIN ($("$PHP_BIN" -r 'echo PHP_VERSION;'))"

"$PHP_BIN" -r 'exit(extension_loaded("pdo_mysql") ? 0 : 1);' \
    || abbruch "Die PHP-Erweiterung pdo_mysql fehlt für $PHP_BIN."

# --- Zielverzeichnis --------------------------------------------------------

schritt "Zielverzeichnis"

ZIEL="${1:-}"
[ -n "$ZIEL" ] || frage_pflicht ZIEL "Installationsverzeichnis" "$PWD/klamottenboerse"
case "$ZIEL" in /*) ;; *) ZIEL="$PWD/$ZIEL" ;; esac

if [ -e "$ZIEL" ] && [ -n "$(ls -A "$ZIEL" 2>/dev/null)" ]; then
    abbruch "$ZIEL existiert bereits und ist nicht leer. Für eine saubere Installation bitte ein leeres oder neues Verzeichnis angeben."
fi
ok "$ZIEL"

# --- Angaben für die .env ---------------------------------------------------

schritt "Allgemein"
frage_pflicht APP_URL "Adresse der Installation (mit https://)" "https://verwaltung.klamottenboerse.de"
APP_URL="${APP_URL%/}"
case "$APP_URL" in https://*) ;; *) warnung "Ohne HTTPS funktionieren Push-Nachrichten nicht." ;; esac

schritt "Datenbank (MySQL/MariaDB, muss bereits angelegt und leer sein)"
while :; do
    frage_pflicht DB_HOST "Host" "localhost"
    frage_pflicht DB_PORT "Port" "3306"
    frage_pflicht DB_DATABASE "Datenbankname"
    frage_pflicht DB_USERNAME "Benutzer"
    frage_geheim DB_PASSWORD "Passwort"

    # Verbindung und "leer" prüfen; Zugangsdaten nur über Umgebungsvariablen übergeben.
    set +e
    DB_PRUEFUNG=$(DBH="$DB_HOST" DBP="$DB_PORT" DBN="$DB_DATABASE" DBU="$DB_USERNAME" DBW="$DB_PASSWORD" "$PHP_BIN" -r '
        try {
            $pdo = new PDO(sprintf("mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4", getenv("DBH"), getenv("DBP"), getenv("DBN")),
                getenv("DBU"), getenv("DBW"), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $anzahl = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()")->fetchColumn();
            echo $anzahl;
            exit($anzahl === 0 ? 0 : 2);
        } catch (Throwable $e) {
            echo $e->getMessage();
            exit(1);
        }' 2>&1)
    DB_STATUS=$?
    set -e

    case "$DB_STATUS" in
        0) ok "Verbindung klappt, Datenbank ist leer"; break ;;
        2) warnung "Die Datenbank enthält bereits $DB_PRUEFUNG Tabelle(n). Für eine saubere Installation bitte eine leere Datenbank verwenden." ;;
        *) warnung "Keine Verbindung: $DB_PRUEFUNG" ;;
    esac
    ja_nein "Andere Zugangsdaten eingeben?" j || abbruch "Datenbank nicht verwendbar."
done

schritt "Mailversand (SMTP)"
frage_pflicht MAIL_HOST "SMTP-Server"
frage_pflicht MAIL_PORT "Port" "587"
case "$MAIL_PORT" in 465) MAIL_SCHEME_VORGABE="smtps" ;; *) MAIL_SCHEME_VORGABE="smtp" ;; esac
frage_pflicht MAIL_SCHEME "Verfahren (smtp = STARTTLS/unverschlüsselt, smtps = SSL)" "$MAIL_SCHEME_VORGABE"
frage_pflicht MAIL_USERNAME "Benutzer"
frage_geheim MAIL_PASSWORD "Passwort"
frage_pflicht MAIL_FROM_ADDRESS "Absender-Adresse" "$MAIL_USERNAME"
frage_pflicht MAIL_FROM_NAME "Absender-Name" "Klamottenbörse"

schritt "Postfach für den Posteingang im Backend (IMAP)"
IMAP_HOST=''; IMAP_PORT='993'; IMAP_ENCRYPTION='ssl'; IMAP_USERNAME=''; IMAP_PASSWORD=''
if ja_nein "Jetzt einrichten? (kann später in der .env ergänzt werden)" j; then
    frage_pflicht IMAP_HOST "IMAP-Server" "$MAIL_HOST"
    frage_pflicht IMAP_PORT "Port" "993"
    frage_pflicht IMAP_ENCRYPTION "Verschlüsselung (ssl/tls/none)" "ssl"
    frage_pflicht IMAP_USERNAME "Benutzer" "$MAIL_USERNAME"
    frage_geheim IMAP_PASSWORD "Passwort (leer = wie SMTP)"
    IMAP_PASSWORD="${IMAP_PASSWORD:-$MAIL_PASSWORD}"
fi

schritt "Übernahme aus V1 (optional)"
V1_DB_HOST=''; V1_DB_PORT='3306'; V1_DB_DATABASE=''; V1_DB_USERNAME=''; V1_DB_PASSWORD=''
if ja_nein "Zugangsdaten der alten V1-Datenbank für den späteren Import hinterlegen?" n; then
    frage_pflicht V1_DB_HOST "Host" "$DB_HOST"
    frage_pflicht V1_DB_PORT "Port" "$DB_PORT"
    frage_pflicht V1_DB_DATABASE "Datenbankname"
    frage_pflicht V1_DB_USERNAME "Benutzer" "$DB_USERNAME"
    frage_geheim V1_DB_PASSWORD "Passwort"
fi

schritt "Admin-Zugang"
frage_pflicht ADMIN_EMAIL "E-Mail-Adresse"
frage_pflicht ADMIN_VORNAME "Vorname"
frage_pflicht ADMIN_NACHNAME "Nachname"

schritt "Zusammenfassung"
info "Verzeichnis:  $ZIEL"
info "Branch:       $BRANCH"
info "Adresse:      $APP_URL"
info "Datenbank:    $DB_USERNAME@$DB_HOST:$DB_PORT/$DB_DATABASE"
info "Mail:         $MAIL_USERNAME über $MAIL_HOST:$MAIL_PORT ($MAIL_SCHEME), Absender $MAIL_FROM_ADDRESS"
info "IMAP:         ${IMAP_HOST:-nicht eingerichtet}"
info "V1-Import:    ${V1_DB_DATABASE:-nicht eingerichtet}"
info "Admin:        $ADMIN_VORNAME $ADMIN_NACHNAME <$ADMIN_EMAIL>"
ja_nein "Installation jetzt starten?" j || abbruch "Abgebrochen, es wurde nichts verändert."

# --- Code holen -------------------------------------------------------------

schritt "Repository klonen"
git clone --branch "$BRANCH" "$REPO_URL" "$ZIEL" \
    || abbruch "Klonen fehlgeschlagen. Existiert der Branch \"$BRANCH\" im Repository?"
cd "$ZIEL"
ok "Stand: $(git log -1 --format='%h %s')"

# --- Composer ---------------------------------------------------------------

schritt "PHP-Abhängigkeiten (Composer)"
if command -v composer >/dev/null 2>&1; then
    COMPOSER=(composer)
    UPDATE_COMPOSER="composer"
    # Sicherstellen, dass Composer dasselbe PHP nutzt wie die Anwendung.
    COMPOSER_PFAD="$(command -v composer)"
    if head -n 1 "$COMPOSER_PFAD" | grep -q 'php' && "$PHP_BIN" "$COMPOSER_PFAD" --version >/dev/null 2>&1; then
        COMPOSER=("$PHP_BIN" "$COMPOSER_PFAD")
        UPDATE_COMPOSER="$PHP_BIN $COMPOSER_PFAD"
    fi
else
    info "Composer nicht gefunden – lade composer.phar herunter (getcomposer.org)."
    ERWARTET="$(curl -fsSL https://composer.github.io/installer.sig)"
    curl -fsSL https://getcomposer.org/installer -o composer-setup.php
    TATSAECHLICH="$("$PHP_BIN" -r "echo hash_file('sha384', 'composer-setup.php');")"
    if [ "$ERWARTET" != "$TATSAECHLICH" ]; then
        rm -f composer-setup.php
        abbruch "Prüfsumme des Composer-Installers stimmt nicht."
    fi
    "$PHP_BIN" composer-setup.php --quiet
    rm -f composer-setup.php
    COMPOSER=("$PHP_BIN" "$ZIEL/composer.phar")
    UPDATE_COMPOSER="$PHP_BIN $ZIEL/composer.phar"
fi

"${COMPOSER[@]}" install --no-dev --optimize-autoloader --no-interaction
"${COMPOSER[@]}" check-platform-reqs --no-dev \
    || abbruch "Es fehlen PHP-Erweiterungen (siehe Liste oben). Bitte beim Hoster aktivieren und das Skript erneut starten."
ok "Abhängigkeiten installiert"

# --- .env schreiben ---------------------------------------------------------

schritt ".env anlegen"
[ -f public/build/manifest.json ] || warnung "public/build fehlt im Repository – vor dem Start auf einem Rechner mit Node \"npm ci && npm run build\" ausführen."

# Die Werte werden von PHP gesetzt (sicheres Quoting, auch bei Sonderzeichen in Passwörtern).
export APP_URL DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD \
    MAIL_HOST MAIL_PORT MAIL_SCHEME MAIL_USERNAME MAIL_PASSWORD MAIL_FROM_ADDRESS MAIL_FROM_NAME \
    IMAP_HOST IMAP_PORT IMAP_ENCRYPTION IMAP_USERNAME IMAP_PASSWORD \
    V1_DB_HOST V1_DB_PORT V1_DB_DATABASE V1_DB_USERNAME V1_DB_PASSWORD \
    BRANCH UPDATE_COMPOSER PHP_BIN
"$PHP_BIN" -r '
    $werte = [
        "APP_ENV" => "production",
        "APP_DEBUG" => "false",
        "APP_URL" => getenv("APP_URL"),
        "LOG_LEVEL" => "warning",
        "DB_CONNECTION" => "mysql",
        "DB_HOST" => getenv("DB_HOST"),
        "DB_PORT" => getenv("DB_PORT"),
        "DB_DATABASE" => getenv("DB_DATABASE"),
        "DB_USERNAME" => getenv("DB_USERNAME"),
        "DB_PASSWORD" => getenv("DB_PASSWORD"),
        "SESSION_SECURE_COOKIE" => str_starts_with(getenv("APP_URL"), "https://") ? "true" : "false",
        "MAIL_MAILER" => "smtp",
        "MAIL_SCHEME" => getenv("MAIL_SCHEME"),
        "MAIL_HOST" => getenv("MAIL_HOST"),
        "MAIL_PORT" => getenv("MAIL_PORT"),
        "MAIL_USERNAME" => getenv("MAIL_USERNAME"),
        "MAIL_PASSWORD" => getenv("MAIL_PASSWORD"),
        "MAIL_FROM_ADDRESS" => getenv("MAIL_FROM_ADDRESS"),
        "MAIL_FROM_NAME" => getenv("MAIL_FROM_NAME"),
        "IMAP_TREIBER" => "imap",
        "IMAP_HOST" => getenv("IMAP_HOST"),
        "IMAP_PORT" => getenv("IMAP_PORT"),
        "IMAP_ENCRYPTION" => getenv("IMAP_ENCRYPTION"),
        "IMAP_USERNAME" => getenv("IMAP_USERNAME"),
        "IMAP_PASSWORD" => getenv("IMAP_PASSWORD"),
        "V1_DB_HOST" => getenv("V1_DB_HOST"),
        "V1_DB_PORT" => getenv("V1_DB_PORT"),
        "V1_DB_DATABASE" => getenv("V1_DB_DATABASE"),
        "V1_DB_USERNAME" => getenv("V1_DB_USERNAME"),
        "V1_DB_PASSWORD" => getenv("V1_DB_PASSWORD"),
        "UPDATE_PHP" => getenv("PHP_BIN"),
        "UPDATE_COMPOSER" => getenv("UPDATE_COMPOSER"),
        "UPDATE_BRANCH" => getenv("BRANCH"),
    ];

    // Einfache Werte unverändert, sonst in Anführungszeichen (ohne ${…}-Auflösung).
    $quote = function (string $wert): string {
        if ($wert === "" || preg_match("/^[A-Za-z0-9_.:\/@+-]+$/", $wert)) {
            return $wert;
        }
        if (! str_contains($wert, "\x27")) {
            return "\x27".$wert."\x27";
        }
        return "\"".addcslashes($wert, "\\\"\$")."\"";
    };

    $zeilen = file(".env.example", FILE_IGNORE_NEW_LINES);
    foreach ($zeilen as $i => $zeile) {
        // auch auskommentierte Einträge wie "# DB_HOST=…" ersetzen
        if (preg_match("/^#?\s*([A-Z0-9_]+)=/", $zeile, $treffer) && array_key_exists($treffer[1], $werte)) {
            $zeilen[$i] = $treffer[1]."=".$quote($werte[$treffer[1]]);
            unset($werte[$treffer[1]]);
        }
    }
    foreach ($werte as $schluessel => $wert) {
        $zeilen[] = $schluessel."=".$quote($wert);
    }
    file_put_contents(".env", implode(PHP_EOL, $zeilen).PHP_EOL);
'
chmod 600 .env
"$PHP_BIN" artisan key:generate --force --no-interaction
ok ".env geschrieben (nur für den Eigentümer lesbar)"

# --- Datenbank & Grunddaten -------------------------------------------------

schritt "Datenbank einrichten"
"$PHP_BIN" artisan migrate --force --no-interaction
for seeder in GrunddatenSeeder MailvorlagenSeeder SeitenSeeder; do
    "$PHP_BIN" artisan db:seed --class="$seeder" --force --no-interaction
done
ok "Tabellen und Grunddaten angelegt"

schritt "Dateien & Rechte"
"$PHP_BIN" artisan storage:link --no-interaction || warnung "storage:link fehlgeschlagen – ggf. beim Hoster Symlinks erlauben."
chmod -R u+rwX,go-w storage bootstrap/cache
ok "storage/ und bootstrap/cache/ beschreibbar"

schritt "Admin-Zugang anlegen"
"$PHP_BIN" artisan admin:anlegen "$ADMIN_EMAIL" --vorname="$ADMIN_VORNAME" --nachname="$ADMIN_NACHNAME" --no-interaction

schritt "Caches aufbauen"
"$PHP_BIN" artisan optimize --no-interaction
ok "fertig"

# --- Abschluss --------------------------------------------------------------

PHP_PFAD="$(command -v "$PHP_BIN")"
printf '\n%s%sInstallation abgeschlossen.%s\n' "$FETT" "$GRUEN" "$NORMAL"
cat <<HINWEISE

  Noch zu erledigen:

  1. Webserver: Die Domain muss auf das Verzeichnis
         $ZIEL/public
     zeigen (nicht auf $ZIEL selbst).

  2. Cron-Job (jede Minute) für Mailversand, Mailplan, Warteliste, Postfach, Push:
         * * * * * cd $ZIEL && $PHP_PFAD artisan schedule:run >> /dev/null 2>&1

  3. Mit dem oben angezeigten Passwort unter $APP_URL/admin anmelden,
     das Passwort ändern und unter „Einstellungen“ die Betreiber-Angaben
     für Impressum und Datenschutz eintragen.

  4. Optional – Daten aus V1 übernehmen:
         cd $ZIEL && $PHP_BIN artisan v1:import --probe
         cd $ZIEL && $PHP_BIN artisan v1:import

  Updates später bequem im Backend unter „System & Fehler“.
HINWEISE
