# Entwicklung

Alles, was man braucht, um am Plugin zu arbeiten oder es zu erweitern. Die Anwenderdokumentation steht
in [README.md](README.md).

## Testinstanz mit Docker

Zum Ausprobieren und Weiterentwickeln liegt ein fertiges WordPress-Setup bei. Es startet WordPress mit
diesem Plugin, setzt Standardwerte und legt drei vollständige Beispiel-Stellenanzeigen in zwei Kategorien an:

```bash
cp .env.example .env      # Port und Adresse anpassen
./bin/dev-setup.sh
```

Am Ende werden alle Adressen ausgegeben, unter anderem die beiden Kategorie-Feeds, die sich direkt in den
[Validator](https://login.empfehlungsbund.de/validator) einfügen lassen.

| Aufruf | Wirkung |
| --- | --- |
| `./bin/dev-setup.sh` | Startet die Instanz. Eine bestehende Installation bleibt unangetastet. |
| `./bin/dev-setup.sh --reset` | Setzt alles zurück – **inklusive Datenbank und Mediathek**. |

Das Plugin ist als Volume eingebunden, Codeänderungen wirken also ohne Neustart. Die Beispieldaten decken
die Fälle ab, die beim Testen erfahrungsgemäß auffallen: eine Fachkraftstelle, eine Schichtstelle mit
unterdrücktem Homeoffice-Feld und eine Ausbildungsstelle. Alle drei liegen über der 1.000-Zeichen-Grenze,
unterhalb derer einige Jobbörsen den Import verweigern.

### Zugangsdaten

Sie werden bei jeder Neuinstallation zufällig erzeugt und in `.dev-credentials` abgelegt (steht in
`.gitignore`). Ein festes Passwort gibt es bewusst nicht: Eine über einen Reverse Proxy erreichbare
WordPress-Instanz wird binnen Minuten von Bots gefunden. Zusätzlich sind `xmlrpc.php` und `wp-cron.php`
gesperrt und die Instanz steht auf `noindex`.

### Betrieb hinter einem Reverse Proxy

WordPress richtet sich hier zur Laufzeit nach dem `Host`-Header, statt eine feste Adresse zu speichern.
Dadurch ist dieselbe Instanz gleichzeitig über `http://localhost:8612` und über den Proxy erreichbar, ohne
Redirect-Schleife. Der Proxy muss dafür den `Host`-Header durchreichen und bei TLS
`X-Forwarded-Proto: https` setzen:

```nginx
location / {
    proxy_pass http://127.0.0.1:8612;
    proxy_set_header Host              $host;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_set_header X-Forwarded-For   $proxy_add_x_forwarded_for;
}
```

Diese Auswertung des `Host`-Headers gehört ausschließlich in die Testinstanz, nicht in eine produktive
Installation.

## Aufbau

`includes/class-eb-fields.php` ist die einzige Quelle für die Feldliste. Beschriftung, Feldtyp,
Hilfetext und die Frage, ob ein Feld einen globalen Standardwert haben kann, stehen dort – Meta-Box,
Einstellungsseite und Feed lesen alle aus demselben Schema. Ein neues Feld ergänzt man dort und sonst nirgends.

| Datei | Aufgabe |
| --- | --- |
| `class-eb-fields.php` | Feldschema, Gruppen, Schlüsselnormalisierung |
| `class-eb-values.php` | Wertermittlung: Beitragswert vor Standardwert vor automatischem Wert |
| `class-eb-feed.php` | Ausgabe der `eb:`-Elemente in den RSS2-Feed |
| `class-eb-metabox.php` | Meta-Box am Beitrag, Speicherroutine |
| `class-eb-settings.php` | Einstellungsseite und globale Standardwerte |
| `class-eb-field-renderer.php` | Rendert ein einzelnes Feld, identisch in Meta-Box und Einstellungen |
| `class-eb-dkz.php` | REST-Route für die Berufssuche der Arbeitsagentur |
| `class-eb-apply.php` | Bewerben-Link ins EBMS: Shortcodes und automatischer Button |
| `class-eb-admin-assets.php` | Lädt `assets/admin.js` und `assets/admin.css` im Backend |

### Berufssuche

Die DKZ-Suche unter `https://login.empfehlungsbund.de/arbeitsagentur/search` ist keine JSON-API: Sie
liefert einen Rails-UJS-Schnipsel mit eingebetteter HTML-Tabelle und sendet keine CORS-Header. Deshalb
läuft die Abfrage über eine eigene REST-Route (`custom-fields-rss/v1/beruf`), die serverseitig parst und
das Ergebnis 12 Stunden zwischenspeichert.

Das Parsen ist entsprechend fragil – ändert sich das Template auf der Gegenseite, liefert
`EB_DKZ::parse()` eine leere Liste und das Feld bleibt manuell befüllbar. Ein JSON-Format auf der
Empfehlungsbund-Seite würde diesen Umweg überflüssig machen.

## Anpassung per Filter

```php
// Feldliste erweitern oder einschränken
add_filter('custom_fields_rss_fields', function ($fields) {
    $fields[] = 'mein-neues-feld';
    return $fields;
});

// Feldaufbau samt Beschriftung und Typ verändern
add_filter('custom_fields_rss_field_schema', function ($schema) {
    $schema['gehalt']['global'] = true;
    return $schema;
});

// Einzelnen Wert kurz vor der Ausgabe im Feed anpassen
add_filter('custom_fields_rss_value', function ($value, $field, $post) {
    return $field === 'tags' ? $value . ', Arbeitgeber des Jahres' : $value;
}, 10, 3);

// Bewerben-Link anpassen, z. B. ein bestimmtes Bewerbungs-Widget vorwählen
add_filter('custom_fields_rss_apply_url', function ($url, $post) {
    return add_query_arg('job_widget_id', 123, $url);
}, 10, 2);

// Beitragstypen überschreiben
add_filter('custom_fields_rss_post_types', function ($types) {
    return array('job');
});
```
