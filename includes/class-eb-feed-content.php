<?php
/**
 * Ergänzt den Stellentext im Feed um Custom Fields. Nötig, wenn ein Page-Builder (z. B. Elementor-Pro-Vorlage)
 * den Text aus Feldern zusammensetzt und der Beitragsinhalt leer bleibt - der Feed enthält sonst nur den Rest.
 */

defined('ABSPATH') || exit;

class EB_Feed_Content {

    public static function init() {
        // Vor Yoast (Priorität 10), damit dessen "appeared first on" hinter dem Stellentext bleibt.
        add_filter('the_content_feed', array(__CLASS__, 'content'), 5);
        add_filter('the_excerpt_rss', array(__CLASS__, 'description'), 5);
    }

    public static function content($content) {
        $extra = self::fields_html(get_post());
        return $extra === '' ? $content : $content . "\n" . $extra;
    }

    /**
     * Statt des 55-Wörter-Auszugs der volle Text. WordPress bildet den Auszug aus dem rohen Beitragsinhalt,
     * der bei Feldern oder reinen Elementor-Seiten leer ist - die description bliebe dann leer.
     */
    public static function description($excerpt) {
        $post = get_post();
        if (!$post || !in_array($post->post_type, EB_Fields::post_types(), true)) {
            return $excerpt;
        }
        $extra     = self::fields_html($post);
        $raw_empty = trim($post->post_content) === '' && trim($post->post_excerpt) === '';
        if ($extra === '' && !$raw_empty) {
            return $excerpt;
        }
        $content = trim(apply_filters('the_content', get_the_content(null, false, $post)));
        return $content === '' ? $extra : $content . "\n" . $extra;
    }

    public static function fields_html($post) {
        $post = get_post($post);
        if (!$post || !in_array($post->post_type, EB_Fields::post_types(), true)) {
            return '';
        }

        $html = '';
        foreach (EB_Settings::feed_fields() as $field) {
            $value = get_post_meta($post->ID, $field['key'], true);
            // Arrays stammen z. B. aus ACF-Repeatern und lassen sich nicht sinnvoll als Text ausgeben.
            if (!is_string($value) || trim($value) === '') {
                continue;
            }
            if ($field['heading'] !== '') {
                $html .= '<h2>' . esc_html($field['heading']) . "</h2>\n";
            }
            $html .= wpautop(strip_shortcodes($value)) . "\n";
        }

        return apply_filters('custom_fields_rss_feed_fields_html', wp_kses_post(trim($html)), $post);
    }

    /** Meta-Keys, die an den Beitragstypen vorkommen - damit Kunden ihre Feldnamen nicht suchen müssen. */
    public static function available_keys() {
        global $wpdb;

        $types = EB_Fields::post_types();
        if (!$types) {
            return array();
        }
        $placeholders = implode(',', array_fill(0, count($types), '%s'));
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $keys = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT pm.meta_key FROM {$wpdb->postmeta} pm
             INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
             WHERE p.post_type IN ($placeholders) AND pm.meta_key NOT LIKE '\\_%%' AND pm.meta_value <> ''
             ORDER BY pm.meta_key LIMIT 200",
            $types
        ));

        $own = array_flip(EB_Fields::keys());
        return array_values(array_filter($keys, function ($key) use ($own) {
            return !isset($own[EB_Fields::normalize_key($key)]);
        }));
    }
}
