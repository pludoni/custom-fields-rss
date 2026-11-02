<?php
/**
 * Legt Stellen an, deren Text nicht (vollständig) im Beitragsinhalt steht. Aufruf per "wp eval-file" aus bin/dev-setup.sh.
 * Jede Stelle steht für eine Konstellation, die bei Kunden vorkommt.
 */

if (get_option('eb_demo_pagebuilder_seeded')) {
    WP_CLI::log('    Page-Builder-Stellen sind bereits angelegt');
    return;
}

$options = (array) get_option('custom_fields_rss_options', array());
$options['post_types'] = array_values(array_unique(array_merge((array) ($options['post_types'] ?? array('post')), array('jobs'))));
update_option('custom_fields_rss_options', $options);
update_option('elementor_cpt_support', array('post', 'page', 'jobs'));

$buckets = array(
    'einleitung' => '<p>Wir planen und errichten Sicherheitstechnik für Rechenzentren, Kliniken und öffentliche Gebäude in ganz Deutschland. Für unser wachsendes Team in Berlin suchen wir Verstärkung in der Projektplanung.</p>',
    'profil'     => '<ul><li><strong>Ausbildung:</strong> Abgeschlossene Ausbildung oder Studium im Bereich Elektrotechnik</li><li><strong>Erfahrung:</strong> Mehrjährige Praxis in der Planung von Brandmelde- oder Einbruchmeldeanlagen</li><li><strong>Normen:</strong> Sicherer Umgang mit DIN 14675 und VdS-Richtlinien</li><li><strong>Arbeitsweise:</strong> Strukturiert, eigenverantwortlich und kommunikationsstark</li></ul>',
    'aufgaben'   => '<ul><li><strong>Projektierung:</strong> Technische Konzeption und detaillierte Planung von Gefahrenmeldeanlagen (BMA, EMA, Videoüberwachung)</li><li><strong>Ausschreibung:</strong> Erstellung von Leistungsverzeichnissen und Bewertung von Angeboten</li><li><strong>Abstimmung:</strong> Enge Zusammenarbeit mit Bauherren, Architekten und Sachverständigen</li><li><strong>Dokumentation:</strong> Pflege von Plänen und Revisionsunterlagen bis zur Abnahme</li></ul>',
    'wir_bieten' => '<ul><li>Unbefristete Festanstellung mit 30 Tagen Urlaub</li><li>Flexible Arbeitszeiten und anteilig mobiles Arbeiten</li><li>Strukturiertes Weiterbildungsprogramm und Herstellerschulungen</li><li>Firmenwagen auch zur privaten Nutzung, Jobrad und Kinderbetreuungszuschuss</li></ul>',
);

/** ACF erkennt einen Wert nur zusammen mit dem Referenz-Meta "_<name>" auf den Feldschlüssel. */
$seed = function ($title, $args, $fields = array(), $meta = array()) {
    $id = wp_insert_post(array_merge(array(
        'post_type'   => 'jobs',
        'post_status' => 'publish',
        'post_title'  => $title,
    ), $args), true);
    if (is_wp_error($id)) {
        WP_CLI::error($id);
    }
    foreach ($fields as $name => $value) {
        update_post_meta($id, $name, $value);
        update_post_meta($id, '_' . $name, 'field_eb_demo_' . $name);
    }
    foreach ($meta as $key => $value) {
        update_post_meta($id, $key, wp_slash($value));
    }
    wp_set_object_terms($id, 'Berlin', 'arbeitsort');
    WP_CLI::log("    #{$id} {$title}");
    return $id;
};

$seed(
    'Projektplaner Sicherheitstechnik (m/w/d) – nur Custom Fields',
    array('post_content' => ''),
    $buckets,
    array('tags' => 'Sicherheitstechnik, Elektrotechnik, Planung, MINT')
);

$seed(
    'Sales Consultant (m/w/d) – Auszug + Custom Fields',
    array('post_content' => '', 'post_excerpt' => 'Die Zusammenarbeit mit Kunden ist deine Passion? Dann bist du hier genau richtig!'),
    $buckets,
    array('tags' => 'Vertrieb, IT, Lizenzen')
);

$seed(
    'Werkstudent IT-Support (m/w/d) – Einleitung im Inhalt, Rest in Custom Fields',
    array('post_content' => $buckets['einleitung']),
    array_diff_key($buckets, array('einleitung' => true)),
    array('tags' => 'Werkstudent, IT-Support, IT')
);

$widget = function ($type, $settings) {
    return array('id' => substr(md5(uniqid('', true)), 0, 7), 'elType' => 'widget', 'widgetType' => $type, 'settings' => $settings, 'elements' => array());
};
$elements = array($widget('text-editor', array('editor' => $buckets['einleitung'])));
foreach (array('profil' => 'Dein Profil', 'aufgaben' => 'Deine Aufgaben', 'wir_bieten' => 'Wir bieten') as $name => $label) {
    $elements[] = $widget('heading', array('title' => $label, 'header_size' => 'h2'));
    $elements[] = $widget('text-editor', array('editor' => $buckets[$name]));
}
$elements[] = $widget('button', array('text' => 'Jetzt bewerben', 'link' => array('url' => '#bewerben')));
$elementor_data = array(array(
    'id'       => substr(md5('section'), 0, 7),
    'elType'   => 'section',
    'settings' => array(),
    'elements' => array(array(
        'id'       => substr(md5('column'), 0, 7),
        'elType'   => 'column',
        'settings' => array('_column_size' => 100),
        'elements' => $elements,
    )),
));

// Im Elementor-Editor gebaut, aber nie dort gespeichert: post_content bleibt leer, der Text steht nur in _elementor_data.
$seed(
    'Linux-Administrator (m/w/d) – Elementor-Editor',
    array('post_content' => ''),
    array(),
    array(
        'tags'                   => 'Linux, Administration, IT',
        '_elementor_edit_mode'   => 'builder',
        '_elementor_template_type' => 'wp-post',
        '_elementor_version'     => defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : '3.0.0',
        '_elementor_data'        => wp_json_encode($elementor_data),
    )
);

update_option('eb_demo_pagebuilder_seeded', 1);
flush_rewrite_rules();
