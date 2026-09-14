# custom-fields-rss – Empfehlungsbund Feederweiterung für WordPress

Die Ausschreibung von Stellenanzeigen und deren Weiterverteilung in über 35 Meta-Jobbörsen erfolgt für
Mitglieder des [Empfehlungsbundes](https://www.empfehlungsbund.de) über eine automatisierte Schnittstelle,
die per XML-RSS-Feed angebunden wird. WordPress bringt einen RSS-Feed von Haus aus mit – ihm fehlen
allerdings die stellenspezifischen Felder wie Ort, Schlagworte oder Bewerbungsweg.

Dieses Plugin ergänzt beides:

* eine **Meta-Box am Beitrag** zur Pflege aller Felder – mit Auswahllisten, Bild-Upload aus der
  Mediathek und einer Suche nach Berufen der Arbeitsagentur (DKZ),
* **globale Standardwerte**, sodass pro Stelle nur noch das Abweichende einzutragen ist,
* die Ausgabe als eigenständige XML-Elemente im Namensraum `eb:`
  („Variante 2“ der [Feed-Spezifikation](https://login.empfehlungsbund.de/validator)).

Ergebnis im Feed:

```xml
<rss version="2.0" xmlns:eb="http://www.empfehlungsbund.de/schema.dtd">
  <channel>
    <item>
      <title>Softwareentwickler Java für eCommerce-Lösungen (m/w/d)</title>
      <link>https://example.de/jobs/4711</link>
      <pubDate>Mon, 03 Jan 2011 09:54:10 +0100</pubDate>
      <description><![CDATA[<p>Vollständiger Stellentext …</p>]]></description>
      <eb:nummer>job-4711</eb:nummer>
      <eb:ort>Dresden</eb:ort>
      <eb:tags>Java, eCommerce, Software, IT</eb:tags>
      <eb:bewerbungsweg>jobs@example.de</eb:bewerbungsweg>
      <eb:kontakt-oeffentlich>Max Mustermann</eb:kontakt-oeffentlich>
      <eb:art>fachkraft</eb:art>
      <eb:homeoffice-art>hybrid</eb:homeoffice-art>
      <eb:beruf>13659</eb:beruf>
    </item>
  </channel>
</rss>
```

## Installation

1. [ZIP herunterladen](https://github.com/pludoni/custom-fields-rss/archive/refs/heads/master.zip) und unter
   `Plugins → Installieren → Plugin hochladen` einspielen, danach aktivieren.
2. Unter `Einstellungen → Empfehlungsbund` die Beitragstypen wählen und die Standardwerte hinterlegen,
   die für alle Stellen gelten (Ort, Ansprechpartner, Bewerbungsweg, Arbeitszeit …).
3. Beitrag anlegen: Der Stellentext kommt in den Beitragsinhalt, die Zusatzfelder in die Meta-Box
   **„Empfehlungsbund Stellenanzeige“** direkt darunter.

Ein zusätzliches Plugin für benutzerdefinierte Felder wird nicht mehr benötigt.

## Woher ein Feldwert kommt

Für jedes Feld gilt in dieser Reihenfolge:

1. der Wert **an der Stellenanzeige** – falls leer:
2. der **globale Standardwert** aus `Einstellungen → Empfehlungsbund` – falls leer:
3. ein **automatischer Fallback**, sofern es für das Feld einen gibt.

Der Platzhalter im Eingabefeld zeigt jeweils an, was im Feed landet, wenn das Feld leer bleibt.

Automatische Fallbacks gibt es für:

| Feld | Fallback |
| --- | --- |
| `nummer` | Die Beitrags-ID (`post-42`). |
| `link` | Der Permalink des Beitrags. |
| `og-image` | Das Beitragsbild, falls gesetzt. |

### Stellennummer / GUID

Die GUID steuert, wie der Importer eine Stelle behandelt: Eine bereits bekannte GUID wird ignoriert,
eine neue GUID erzeugt eine neue Anzeige mit eigener Zugriffsstatistik. In den Einstellungen legen Sie
fest, was bei automatisch vergebenen Nummern passieren soll:

* **Gleich bleibend je Beitrag** (Standard) – eine Stelle bleibt im Portal dieselbe Anzeige.
* **Neu bei jeder Änderung** – jede Überarbeitung wird als neue Anzeige geführt und separat ausgewertet.

Wer die Nummern selbst vergibt, trägt sie einfach ins Feld ein; die Einstellung greift dann nicht.

## Besondere Felder

**Bilder (`og-image`, `header-image`).** Über „Bild auswählen“ öffnet sich die WordPress-Mediathek.
Dort hochgeladene Bilder liegen wie jede andere Mediendatei unter `wp-content/uploads/` und sind
öffentlich erreichbar – ihre URL wird in den Feed geschrieben. Alternativ lässt sich eine externe URL
direkt eintragen.

**Beruf (`beruf`).** Das Suchfeld fragt die DKZ-Berufsliste der Bundesagentur für Arbeit ab und schreibt
die gewählte ID ins Feld. Die Unterscheidung zwischen Ausbildungs- und Tätigkeitsberufen erfolgt
automatisch anhand der gewählten Stellenart – bei `ausbildung` und `duales_studium` wird in den
Ausbildungsberufen gesucht, sonst in den Tätigkeitsberufen. Ergebnisse werden 12 Stunden zwischengespeichert.

## Feldreferenz

Maßgeblich ist immer die [Feed-Spezifikation im Validator](https://login.empfehlungsbund.de/validator).

### Pflichtangaben

| Feld | Bedeutung |
| --- | --- |
| `title` | Aussagekräftiger Titel mit Position, Kernkompetenz und Domäne. Nicht „Softwareentwickler (m/w/d)“, sondern „Softwareentwickler Java für eCommerce-Lösungen (m/w/d)“. |
| `link` | Ziel der Stellenanzeige. HTML ist PDF vorzuziehen – HTML-Seiten werden auf der Jobdetailseite als iFrame eingebettet. |
| `pubDate` | Veröffentlichungsdatum im RFC-822-Format. Liefert WordPress automatisch. |
| `description` | Vollständiger Stellentext. Einige Jobbörsen importieren erst ab ca. 1.000 Zeichen. Erlaubte HTML-Tags: `p, q, strong, em, li, ol, ul`. |
| `nummer` | Eindeutige GUID pro Stelle, siehe oben. |
| `ort` | Arbeitsort. Stellen ohne Ort aus der Region werden nicht importiert. |
| `tags` | Ca. 4 kommagetrennte Schlagworte, die wichtigsten zuerst. **Genau eines** aus `IT` (Softwareentwicklung), `MINT` (Maschinenbau, Mechatronik, Elektrotechnik, Physik, Chemie, Technik), `OFFICE` (kaufmännisch/Verwaltung) oder `SANO` (Gesundheits- und Sozialwesen) steuert das Zielportal. |
| `bewerbungsweg` + `kontakt-oeffentlich` | Bewerbungsweg: genau eine E-Mail-Adresse oder ein https-Link zum Bewerbermanagementsystem. Kontakt-oeffentlich: öffentlich angezeigte Ansprechpartner (kommagetrennt); deren E-Mail sollte mit der im Empfehlungsbund-Profil hinterlegten übereinstimmen. Beide Felder nur gemeinsam verwenden. |
| `kontakt` | **Veraltet.** Nur nötig, wenn `bewerbungsweg`/`kontakt-oeffentlich` nicht verwendet werden. Das Feld erscheint in der Meta-Box nur, solange ein Wert hinterlegt ist. |

### Optionale Angaben

| Feld | Werte / Beispiel |
| --- | --- |
| `strasse-nr`, `plz`, `bundesland` | Nur nötig bei mehreren Standorten in einer Stadt oder wenn viele Orte dynamisch angelegt werden sollen. Sonst reicht `ort`. |
| `einrichtung` | Einrichtung/Unterprofil – muss im Empfehlungsbund-Profil existieren. |
| `gehalt` | `30.000 EUR`, `30000`, `30.000 EUR - 50.000 EUR pro Jahr`, `3.000-4.000 pro monat`, `15 EUR / h`. Ohne Zeiteinheit gilt Jahresgehalt. |
| `arbeitszeit` | `[ART] [VON]-[BIS]` mit Art `fest`, `flexibel`, `schicht` – z. B. `fest 40h`, `flexibel 32-40h`. |
| `urlaub` | `30`, `30 Tage`, `21-30 Tage` |
| `art` | `fachkraft`, `fuehrungskraft`, `freelancer`, `hilfskraft`, `praktikum`, `werkstudent`, `volunteer`, `ausbildung`, `weiterbildung`, `duales_studium` |
| `homeoffice-art` | `hybrid` (teilweise), `full` (deutschlandweit remote), `full (DE,AT,UK)` (mit erlaubten Wohnorten) |
| `levels` | `junior`, `intermediate`, `senior`, `ungelernt`, `quereinstieg` – mehrere kommagetrennt |
| `sprachen` | Sprache oder ISO-Code + CEFRL-Level, max. 3: `Deutsch C2, Englisch B2`, `de (B2)` |
| `schwerbehinderung` | `geeignet` (auch für Schwerbehinderte) oder `exklusiv` (nur Schwerbehinderte). Ohne Angabe: keine Spezifikation. |
| `beruf` | DKZ-Berufs-ID der Arbeitsagentur (z. B. `13659` für Fachinformatiker Anwendungsentwicklung). |
| `og-image` | Vorschaubild für Social Media. JPEG/GIF/PNG, empfohlen 1080px Breite, mind. 600px. |
| `header-image` | Bannerbild in den Community-Portalen. JPEG/GIF/PNG, empfohlen 1100px Breite. |
| `relocation` | `ZIELGRUPPE: FREITEXT` mit Zielgruppe `international` oder `eu` – z. B. `international: We help you with the Blue Card and pay the flight!` |

## Feed-URLs

Der Standard-Feed liegt unter `https://www.example.com/?feed=rss2`. Sollen Praktikanten-/Lernenden-Stellen
und Stellen für Fach- und Führungskräfte getrennt übergeben werden, legen Sie dafür zwei Kategorien an;
deren Feeds erreichen Sie mit der jeweiligen Kategorie-ID:

```
https://www.example.com/?cat=5&feed=rss2
```

Diese Feed-URLs anschließend unter <https://login.empfehlungsbund.de/validator> prüfen und importieren lassen.

## Umstieg von Version 1.x

Version 1.x wurde zusammen mit dem Plugin
[Custom Field Template](https://wordpress.org/plugins/custom-field-template/) eingesetzt. Bestehende Werte
bleiben erhalten: Die Meta-Box liest dieselben Meta-Schlüssel, Groß-/Kleinschreibung ist egal und
Unterstriche werden auf Bindestriche normalisiert (`Homeoffice_Art` → `eb:homeoffice-art`). Beim Speichern
eines Beitrags werden abweichende Schreibweisen desselben Feldes zusammengeführt, damit ein Feld nicht
doppelt im Feed landet.

Custom Field Template kann nach dem Update deaktiviert werden. Die dort gepflegten `default`-Werte
entsprechen den Standardwerten unter `Einstellungen → Empfehlungsbund`.

## Anpassung per Filter

```php
// Feldliste erweitern oder einschränken
add_filter('custom_fields_rss_fields', function ($fields) {
    $fields[] = 'mein-neues-feld';
    return $fields;
});

// Kompletten Feldaufbau samt Beschriftung und Typ verändern
add_filter('custom_fields_rss_field_schema', function ($schema) {
    $schema['gehalt']['global'] = true;
    return $schema;
});

// Einzelnen Wert kurz vor der Ausgabe im Feed anpassen
add_filter('custom_fields_rss_value', function ($value, $field, $post) {
    return $field === 'tags' ? $value . ', Arbeitgeber des Jahres' : $value;
}, 10, 3);

// Beitragstypen überschreiben
add_filter('custom_fields_rss_post_types', function ($types) {
    return array('job');
});
```

## Einbettung als iFrame ermöglichen

Unter den Stellenanzeigen in den Community-Portalen wird Ihre eigene Stellenseite als iFrame eingebettet.
Viele Webserver und CMS unterbinden das per `X-Frame-Options` bzw. `Content-Security-Policy`. Damit die
Einbettung funktioniert, muss `X-Frame-Options` entfernt werden und in der `Content-Security-Policy` eine
Whitelist der Portale hinterlegt sein – die aktuelle Liste finden Sie unten auf der
[Validator-Seite](https://login.empfehlungsbund.de/validator).

## Kompatibilität

Getestet mit WordPress 6.x/7.x und PHP 7.4 bis 8.3, im Block- wie im klassischen Editor.

## Changelog

**2.1.0**

* Meta-Box „Empfehlungsbund Stellenanzeige“ am Beitrag – kein zusätzliches Feld-Plugin mehr nötig.
* Globale Standardwerte und Einstellungsseite unter `Einstellungen → Empfehlungsbund`.
* Automatische Fallbacks für Nummer, Link und Social-Media-Bild.
* Bildfelder mit Mediathek-Anbindung, Berufsfeld mit DKZ-Suche.
* Filter `custom_fields_rss_field_schema`, `custom_fields_rss_value` und `custom_fields_rss_post_types`.

**2.0.0**

* Fatal Error unter PHP 8 behoben – der Feed brach dort komplett ab (Callbacks waren nicht `static`).
* Nur noch die dokumentierten Empfehlungsbund-Felder werden ausgegeben; interne Meta-Daten anderer Plugins
  landen nicht mehr im öffentlichen Feed und erzeugen kein ungültiges XML mehr.
* Filter `custom_fields_rss_fields` zum Erweitern der Feldliste.
* Feldnamen werden normalisiert (`Homeoffice_Art` → `eb:homeoffice-art`).

**1.0**

* Erstveröffentlichung.

## Lizenz

GPL-2.0, pludoni GmbH
