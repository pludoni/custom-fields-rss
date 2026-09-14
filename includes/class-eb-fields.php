<?php
/** Feldschema laut https://login.empfehlungsbund.de/validator - einzige Quelle für Feed, Meta-Box und Einstellungen. */

defined('ABSPATH') || exit;

class EB_Fields {

    public static function all() {
        $fields = array(
            'nummer' => array(
                'label'       => 'Nummer / GUID',
                'type'        => 'text',
                'group'       => 'pflicht',
                'global'      => false,
                'placeholder' => 'automatisch',
                'help'        => 'Eindeutig pro Stelle. Leer lassen: das Plugin vergibt die Nummer automatisch.',
            ),
            'ort' => array(
                'label'  => 'Arbeitsort',
                'type'   => 'text',
                'group'  => 'pflicht',
                'global' => true,
                'help'   => 'Stellen ohne Ort aus der Region werden nicht importiert.',
            ),
            'tags' => array(
                'label'       => 'Schlagworte',
                'type'        => 'text',
                'group'       => 'pflicht',
                'global'      => true,
                'placeholder' => 'Java, eCommerce, Software, IT',
                'help'        => 'Ca. 4 Schlagworte, wichtigste zuerst. Genau eines aus IT, MINT, OFFICE oder SANO steuert das Zielportal.',
            ),
            'bewerbungsweg' => array(
                'label'       => 'Bewerbungsweg',
                'type'        => 'text',
                'group'       => 'pflicht',
                'global'      => true,
                'placeholder' => 'bewerbung@firma.de',
                'help'        => 'Genau eine E-Mail-Adresse oder ein https-Link zum Bewerbermanagementsystem.',
            ),
            'kontakt-oeffentlich' => array(
                'label'  => 'Ansprechpartner (öffentlich)',
                'type'   => 'text',
                'group'  => 'pflicht',
                'global' => true,
                'help'   => 'Kommagetrennt. Die E-Mail sollte mit der im Empfehlungsbund-Profil hinterlegten übereinstimmen.',
            ),
            'link' => array(
                'label'       => 'Link zur Stelle',
                'type'        => 'text',
                'group'       => 'pflicht',
                'global'      => false,
                'placeholder' => 'Permalink des Beitrags',
                'help'        => 'Nur ausfüllen, wenn die Stelle woanders liegt als dieser Beitrag.',
            ),

            'art' => array(
                'label'   => 'Stellenart',
                'type'    => 'select',
                'group'   => 'optional',
                'global'  => true,
                'options' => array(
                    'fachkraft'      => 'Fachkraft',
                    'fuehrungskraft' => 'Führungskraft',
                    'freelancer'     => 'Freelancer',
                    'hilfskraft'     => 'Hilfskraft',
                    'praktikum'      => 'Praktikum',
                    'werkstudent'    => 'Werkstudent',
                    'volunteer'      => 'Volunteer',
                    'ausbildung'     => 'Ausbildung',
                    'weiterbildung'  => 'Weiterbildung',
                    'duales_studium' => 'Duales Studium',
                ),
            ),
            'homeoffice-art' => array(
                'label'    => 'Homeoffice',
                'type'     => 'datalist',
                'group'    => 'optional',
                'global'   => true,
                'options'  => array('hybrid', 'full', 'full (DE,AT,UK)'),
                'help'     => 'hybrid = teilweise, full = deutschlandweit remote, full (DE,AT) = mit erlaubten Wohnorten.',
            ),
            'levels' => array(
                'label'   => 'Karrierestufe',
                'type'    => 'checkboxes',
                'group'   => 'optional',
                'global'  => true,
                'options' => array(
                    'junior'       => 'Junior',
                    'intermediate' => 'Intermediate',
                    'senior'       => 'Senior',
                    'ungelernt'    => 'Ungelernt',
                    'quereinstieg' => 'Quereinstieg',
                ),
            ),
            'gehalt' => array(
                'label'       => 'Gehalt',
                'type'        => 'text',
                'group'       => 'optional',
                'global'      => false,
                'placeholder' => '50.000 EUR - 65.000 EUR pro Jahr',
                'help'        => 'Ohne Zeiteinheit wird ein Jahresgehalt angenommen. Auch: 3.000-4.000 pro monat, 15 EUR / h.',
            ),
            'arbeitszeit' => array(
                'label'       => 'Arbeitszeit',
                'type'        => 'text',
                'group'       => 'optional',
                'global'      => true,
                'placeholder' => 'fest 40h',
                'help'        => 'Format [ART] [VON]-[BIS] mit fest, flexibel oder schicht.',
            ),
            'urlaub' => array(
                'label'       => 'Urlaubstage',
                'type'        => 'text',
                'group'       => 'optional',
                'global'      => true,
                'placeholder' => '30 Tage',
            ),
            'sprachen' => array(
                'label'       => 'Sprachanforderungen',
                'type'        => 'text',
                'group'       => 'optional',
                'global'      => true,
                'placeholder' => 'Deutsch C1, Englisch B2',
                'help'        => 'Maximal 3 Angaben, Level nach CEFRL (A1-C2).',
            ),
            'beruf' => array(
                'label'  => 'Beruf (DKZ)',
                'type'   => 'beruf',
                'group'  => 'optional',
                'global' => false,
                'help'   => 'Für den Export an die Bundesagentur für Arbeit. Suchbegriff eingeben und Beruf auswählen.',
            ),
            'schwerbehinderung' => array(
                'label'   => 'Schwerbehinderung',
                'type'    => 'select',
                'group'   => 'optional',
                'global'  => true,
                'options' => array(
                    'geeignet' => 'Geeignet (auch für Schwerbehinderte)',
                    'exklusiv' => 'Exklusiv (nur Schwerbehinderte)',
                ),
            ),
            'relocation' => array(
                'label'       => 'Relocation-Support',
                'type'        => 'text',
                'group'       => 'optional',
                'global'      => true,
                'placeholder' => 'international: We pay the flight!',
                'help'        => 'Format ZIELGRUPPE: FREITEXT mit Zielgruppe international oder eu.',
            ),
            'og-image' => array(
                'label'  => 'Social-Media-Bild',
                'type'   => 'media',
                'group'  => 'optional',
                'global' => true,
                'help'   => 'Empfohlen 1080px Breite, mindestens 600px. Ohne Angabe wird das Beitragsbild verwendet.',
            ),
            'header-image' => array(
                'label'  => 'Bannerbild',
                'type'   => 'media',
                'group'  => 'optional',
                'global' => true,
                'help'   => 'Banner auf den Community-Portalen, empfohlen 1100px Breite.',
            ),

            'einrichtung' => array(
                'label'  => 'Einrichtung / Unterprofil',
                'type'   => 'text',
                'group'  => 'adresse',
                'global' => true,
                'help'   => 'Muss im Empfehlungsbund-Profil hinterlegt sein.',
            ),
            'strasse-nr' => array('label' => 'Straße und Hausnummer', 'type' => 'text', 'group' => 'adresse', 'global' => true),
            'plz'        => array('label' => 'PLZ',                   'type' => 'text', 'group' => 'adresse', 'global' => true),
            'bundesland' => array('label' => 'Bundesland',            'type' => 'text', 'group' => 'adresse', 'global' => true),

            'kontakt' => array(
                'label'  => 'Kontakt (veraltet)',
                'type'   => 'text',
                'group'  => 'veraltet',
                'global' => true,
                'help'   => 'Nur nötig, wenn Bewerbungsweg und Ansprechpartner nicht verwendet werden.',
            ),
        );

        return apply_filters('custom_fields_rss_field_schema', $fields);
    }

    public static function keys() {
        return apply_filters('custom_fields_rss_fields', array_keys(self::all()));
    }

    public static function groups() {
        return array(
            'pflicht'  => 'Pflichtangaben',
            'optional' => 'Weitere Angaben',
            'adresse'  => 'Adresse und Einrichtung',
            'veraltet' => 'Veraltet',
        );
    }

    /** "Homeoffice_Art" und "homeoffice-art" meinen dasselbe Feld. */
    public static function normalize_key($key) {
        return str_replace('_', '-', strtolower(trim($key)));
    }

    public static function post_types() {
        $types = EB_Settings::get('post_types', array('post'));
        return apply_filters('custom_fields_rss_post_types', (array) $types);
    }
}
