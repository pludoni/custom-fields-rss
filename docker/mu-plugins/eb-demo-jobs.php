<?php
/**
 * Plugin Name: EB Demo – Stellen mit Page-Builder-Inhalten
 * Description: Nur für die Docker-Testinstanz. Bildet Kundenseiten nach, deren Stellentext nicht im Beitragsinhalt steht (z. B. ibh.de: Elementor-Pro-Vorlage + Custom Fields).
 */

defined('ABSPATH') || exit;

const EB_DEMO_BUCKETS = array(
    'einleitung' => 'Einleitung',
    'profil'     => 'Dein Profil',
    'aufgaben'   => 'Deine Aufgaben',
    'wir_bieten' => 'Wir bieten',
);

add_action('init', function () {
    register_post_type('jobs', array(
        'label'       => 'Stellenangebote',
        'labels'      => array('singular_name' => 'Stellenangebot'),
        'public'      => true,
        'has_archive' => 'stellenangebote',
        'rewrite'     => array('slug' => 'jobs'),
        'menu_icon'   => 'dashicons-id',
        // Wie bei ibh.de: nicht in der REST-API, also auch nicht im Block-Editor.
        'show_in_rest' => false,
        'supports'    => array('title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'elementor'),
    ));
    register_taxonomy('arbeitsort', 'jobs', array('label' => 'Arbeitsort', 'public' => true));
});

add_action('acf/init', function () {
    $fields = array();
    foreach (EB_DEMO_BUCKETS as $name => $label) {
        $fields[] = array(
            'key'   => 'field_eb_demo_' . $name,
            'name'  => $name,
            'label' => $label,
            'type'  => 'wysiwyg',
        );
    }
    acf_add_local_field_group(array(
        'key'      => 'group_eb_demo_job',
        'title'    => 'Stelleninhalte',
        'fields'   => $fields,
        'location' => array(array(array('param' => 'post_type', 'operator' => '==', 'value' => 'jobs'))),
    ));
});

// Ersetzt die Theme-Builder-Vorlage von Elementor Pro: die Buckets erscheinen nur auf der Seite, nie in the_content.
add_filter('template_include', function ($template) {
    return is_singular('jobs') ? __DIR__ . '/eb-demo/single-jobs.php' : $template;
});
