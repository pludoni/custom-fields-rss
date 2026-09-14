<?php
/** Lädt Media-Uploader und Berufssuche nur dort, wo die Felder auch auftauchen. */

defined('ABSPATH') || exit;

class EB_Admin_Assets {

    public static function init() {
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue'));
    }

    public static function enqueue($hook) {
        $is_settings = $hook === 'settings_page_custom-fields-rss';
        $is_editor   = in_array($hook, array('post.php', 'post-new.php'), true)
            && in_array(get_post_type(), EB_Fields::post_types(), true);

        if (!$is_settings && !$is_editor) {
            return;
        }

        wp_enqueue_media();

        $base = plugin_dir_url(CUSTOM_FIELDS_RSS_FILE) . 'assets/';

        wp_enqueue_style('custom-fields-rss-admin', $base . 'admin.css', array(), CUSTOM_FIELDS_RSS_VERSION);
        wp_enqueue_script('custom-fields-rss-admin', $base . 'admin.js', array('jquery'), CUSTOM_FIELDS_RSS_VERSION, true);

        wp_localize_script('custom-fields-rss-admin', 'customFieldsRss', array(
            'restUrl' => esc_url_raw(rest_url('custom-fields-rss/v1/beruf')),
            'nonce'   => wp_create_nonce('wp_rest'),
        ));
    }
}
