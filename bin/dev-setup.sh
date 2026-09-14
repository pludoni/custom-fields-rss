#!/usr/bin/env bash
# Startet eine WordPress-Testinstanz mit diesem Plugin und legt Beispiel-Stellenanzeigen an.
set -euo pipefail

cd "$(dirname "$0")/.."

[ -f .env ] && set -a && . ./.env && set +a
WP_PORT="${WP_PORT:-8612}"
WP_URL="${WP_URL:-http://localhost:${WP_PORT}}"

ADMIN_USER="${ADMIN_USER:-ebadmin}"
CREDENTIALS_FILE=".dev-credentials"

random_secret() {
    openssl rand -base64 24 | tr -dc 'A-Za-z0-9' | cut -c1-24
}

wp() {
    docker compose run --rm -T cli wp "$@"
}

if [ "${1:-}" = "--reset" ]; then
    echo "==> Bestehende Instanz wird gelöscht"
    docker compose down -v
    rm -f "${CREDENTIALS_FILE}"
fi

echo "==> Container starten"
docker compose up -d db wp

echo "==> Auf WordPress warten"
for _ in $(seq 1 60); do
    if docker compose exec -T wp test -f /var/www/html/wp-config.php 2>/dev/null; then
        break
    fi
    sleep 2
done

if wp core is-installed 2>/dev/null; then
    echo "==> WordPress ist bereits installiert, Beispieldaten werden übersprungen"
    wp plugin activate custom-fields-rss >/dev/null
    echo
    echo "    Adresse:       ${WP_URL}"
    echo "    Zugangsdaten:  ${CREDENTIALS_FILE}"
    exit 0
fi

# Die Instanz ist über den Reverse Proxy öffentlich erreichbar und wird entsprechend schnell
# von Bots gefunden - deshalb nie ein festes Passwort.
ADMIN_PASS="$(random_secret)"

echo "==> WordPress installieren"
wp core install \
    --url="${WP_URL}" \
    --title="Empfehlungsbund Plugin-Test" \
    --admin_user="${ADMIN_USER}" \
    --admin_password="${ADMIN_PASS}" \
    --admin_email="admin@example.de" \
    --skip-email

wp option update blogdescription "Testinstanz für die Empfehlungsbund Feederweiterung"
# Sendet noindex als Meta-Tag und X-Robots-Tag, damit die Instanz nicht in Suchmaschinen landet.
wp option update blog_public 0
wp language core install de_DE --activate || true
wp rewrite structure '/%postname%/' --hard
wp plugin activate custom-fields-rss

echo "==> Globale Standardwerte setzen"
wp option update custom_fields_rss_options --format=json <<'JSON'
{
  "defaults": {
    "ort": "Dresden",
    "bewerbungsweg": "bewerbung@example.de",
    "kontakt-oeffentlich": "Max Mustermann",
    "arbeitszeit": "flexibel 32-40h",
    "urlaub": "30 Tage",
    "homeoffice-art": "hybrid",
    "strasse-nr": "Königsbrücker Straße 96",
    "plz": "01099",
    "bundesland": "Sachsen"
  },
  "nummer_strategy": "stable",
  "post_types": ["post"]
}
JSON

echo "==> Beispielinhalte von WordPress entfernen"
HELLO=$(wp post list --post_type=post --name=hello-world --format=ids)
[ -n "${HELLO}" ] && wp post delete "${HELLO}" --force >/dev/null

echo "==> Kategorien anlegen"
FACH=$(wp term create category "Fach- und Führungskräfte" --slug=fachkraefte --porcelain)
AUSB=$(wp term create category "Ausbildung und Praktikum" --slug=ausbildung --porcelain)

seed_post() {
    local title="$1" cat_id="$2" content="$3"
    shift 3
    local id
    id=$(wp post create --post_title="${title}" --post_status=publish --post_category="${cat_id}" --porcelain --post_content="${content}")
    while [ "$#" -gt 0 ]; do
        wp post meta add "${id}" "$1" "$2" >/dev/null
        shift 2
    done
    echo "    #${id} ${title}"
}

echo "==> Beispiel-Stellenanzeigen anlegen"

seed_post "Softwareentwickler Java für eCommerce-Lösungen (m/w/d)" "${FACH}" \
'<p>Wir entwickeln seit über 15 Jahren eCommerce-Plattformen für den Mittelstand. Unsere Systeme verarbeiten täglich mehrere hunderttausend Bestellungen und sind für unsere Kunden geschäftskritisch. Für den Ausbau unserer Plattform suchen wir Verstärkung im Backend-Team.</p><p><strong>Ihre Aufgaben</strong></p><ul><li>Weiterentwicklung unserer Shop-Plattform auf Basis von Java 21 und Spring Boot</li><li>Entwurf und Umsetzung von REST-Schnittstellen zu Warenwirtschafts- und Zahlungssystemen</li><li>Optimierung von Datenbankzugriffen und Caching-Strategien unter hoher Last</li><li>Code-Reviews, Pair Programming und die fachliche Begleitung neuer Kolleginnen und Kollegen</li><li>Mitarbeit an der schrittweisen Ablösung unseres Monolithen durch fachlich geschnittene Services</li></ul><p><strong>Das bringen Sie mit</strong></p><ul><li>Mehrjährige Berufserfahrung in der Entwicklung mit Java und Spring</li><li>Sicherer Umgang mit relationalen Datenbanken, idealerweise PostgreSQL</li><li>Erfahrung mit Docker, CI/CD-Pipelines und automatisierten Tests</li><li>Freude daran, fachliche Anforderungen gemeinsam mit dem Produktteam zu schärfen</li><li>Sehr gute Deutschkenntnisse und gute Englischkenntnisse</li></ul><p><strong>Was wir bieten</strong></p><ul><li>Unbefristete Festanstellung mit flexibler Arbeitszeit und anteiligem Homeoffice</li><li>Moderne Hardware nach Wahl und ein jährliches Weiterbildungsbudget</li><li>Ein Team, das Wert auf saubere Architektur und Wissensaustausch legt</li><li>Jobticket, Zuschuss zur betrieblichen Altersvorsorge und 30 Tage Urlaub</li></ul><p>Wir freuen uns auf Ihre Bewerbung mit Angabe Ihres frühestmöglichen Eintrittstermins.</p>' \
    tags "Java, Spring Boot, eCommerce, IT" \
    art "fachkraft" \
    levels "senior,intermediate" \
    gehalt "60.000 EUR - 78.000 EUR pro Jahr" \
    sprachen "Deutsch C1, Englisch B2" \
    beruf "13659"

seed_post "Mechatroniker für die Instandhaltung von Produktionsanlagen (m/w/d)" "${FACH}" \
'<p>An unserem Standort in Dresden fertigen wir Komponenten für die Halbleiterindustrie. Unsere Produktionsanlagen laufen im Dreischichtbetrieb, ihre Verfügbarkeit entscheidet über unsere Liefertreue. Für die Instandhaltung suchen wir eine erfahrene Fachkraft.</p><p><strong>Ihre Aufgaben</strong></p><ul><li>Wartung, Inspektion und Instandsetzung unserer Fertigungs- und Prüfanlagen</li><li>Eingrenzung und Behebung von Störungen an mechanischen, pneumatischen und elektrischen Baugruppen</li><li>Analyse wiederkehrender Ausfälle und Ableitung vorbeugender Maßnahmen</li><li>Begleitung von Umbauten und der Inbetriebnahme neuer Anlagen</li><li>Dokumentation der Instandhaltungsmaßnahmen im Wartungssystem</li></ul><p><strong>Das bringen Sie mit</strong></p><ul><li>Abgeschlossene Ausbildung als Mechatroniker, Industriemechaniker oder Elektroniker für Betriebstechnik</li><li>Erfahrung in der Instandhaltung von Produktionsanlagen, idealerweise im Reinraumumfeld</li><li>Kenntnisse in der Fehlersuche an SPS-gesteuerten Anlagen</li><li>Bereitschaft zur Schichtarbeit und zur Teilnahme an der Rufbereitschaft</li><li>Sorgfältige, eigenverantwortliche Arbeitsweise</li></ul><p><strong>Was wir bieten</strong></p><ul><li>Tarifliche Vergütung mit Schichtzulagen und Urlaubsgeld</li><li>Strukturierte Einarbeitung durch einen festen Paten im Team</li><li>Regelmäßige Herstellerschulungen an unseren Anlagen</li><li>Betriebliche Altersvorsorge, Jobrad und Zuschuss zum Mittagessen</li></ul>' \
    tags "Instandhaltung, SPS, Automatisierung, MINT" \
    art "fachkraft" \
    arbeitszeit "schicht 38,5 Stunden" \
    homeoffice-art "-" \
    beruf "2862"

seed_post "Ausbildung zum Fachinformatiker Anwendungsentwicklung (m/w/d)" "${AUSB}" \
'<p>Du willst wissen, wie aus einer Idee eine Software wird, die täglich von tausenden Menschen genutzt wird? Bei uns lernst du das von Anfang an im echten Projekt statt in der Übungsaufgabe.</p><p><strong>Das lernst du bei uns</strong></p><ul><li>Grundlagen der Programmierung und schrittweise die Arbeit an unseren produktiven Anwendungen</li><li>Wie man Anforderungen mit Fachabteilungen klärt und in Software übersetzt</li><li>Arbeit mit Versionsverwaltung, automatisierten Tests und Code-Reviews</li><li>Datenbankmodellierung und der Umgang mit Schnittstellen</li><li>Wie ein Team gemeinsam plant, umsetzt und ausliefert</li></ul><p><strong>Das solltest du mitbringen</strong></p><ul><li>Guten Realschulabschluss oder Abitur, gern mit Freude an Mathematik</li><li>Erste eigene Programmierversuche, egal in welcher Sprache</li><li>Lust, Dinge selbst auszuprobieren und nachzufragen, wenn etwas unklar ist</li><li>Gute Deutschkenntnisse in Wort und Schrift</li></ul><p><strong>Was wir bieten</strong></p><ul><li>Feste Ansprechperson im Team über die gesamte Ausbildung hinweg</li><li>Übernahme nach erfolgreichem Abschluss ist bei uns die Regel</li><li>Übernahme der Kosten für Fachliteratur und das Jobticket</li><li>Eigener Rechner, eigene Aufgaben und ein Team, das Fragen ernst nimmt</li></ul><p>Bewirb dich einfach mit Zeugnis und ein paar Sätzen dazu, was dich an Softwareentwicklung interessiert.</p>' \
    tags "Ausbildung, Softwareentwicklung, IT" \
    art "ausbildung" \
    levels "ungelernt" \
    gehalt "1.100 EUR pro monat" \
    beruf "13659"

cat > "${CREDENTIALS_FILE}" <<EOF
# Zugangsdaten der Testinstanz - steht in .gitignore, nicht einchecken.
# Wird bei "bin/dev-setup.sh --reset" neu erzeugt.

WordPress-Login: ${ADMIN_USER} / ${ADMIN_PASS}
EOF
chmod 600 "${CREDENTIALS_FILE}"

echo
echo "==> Fertig"
echo
echo "    Adresse:       ${WP_URL}"
echo "    Login:         ${WP_URL}/wp-admin/  -  Zugangsdaten in ${CREDENTIALS_FILE}"
echo "    Einstellungen: ${WP_URL}/wp-admin/options-general.php?page=custom-fields-rss"
echo
echo "    Feed gesamt:       ${WP_URL}/feed/"
echo "    Fach-/Führung:     ${WP_URL}/category/fachkraefte/feed/"
echo "    Ausbildung:        ${WP_URL}/category/ausbildung/feed/"
echo
echo "    Validator:    https://login.empfehlungsbund.de/validator"
