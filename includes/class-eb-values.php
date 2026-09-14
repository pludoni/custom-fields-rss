<?php
/** Wertermittlung: Beitragswert vor globalem Standardwert vor automatischem Fallback. */

defined('ABSPATH') || exit;

class EB_Values {

    /** Unterdrückt Standardwert und Fallback für ein einzelnes Feld. */
    const SUPPRESS = '-';

    /** Alle gesetzten Meta-Werte eines Beitrags, Schlüssel normalisiert. */
    public static function raw_meta($post_id) {
        $out = array();
        foreach ((array) get_post_custom($post_id) as $key => $values) {
            if (strpos($key, '_') === 0) {
                continue;
            }
            $value = trim((string) reset($values));
            if ($value !== '') {
                $out[EB_Fields::normalize_key($key)] = $value;
            }
        }
        return $out;
    }

    public static function raw($post_id, $field) {
        $meta = self::raw_meta($post_id);
        return isset($meta[$field]) ? $meta[$field] : '';
    }

    /** Endgültige Feldwerte für den Feed, inklusive Standardwerten und Fallbacks. */
    public static function resolved($post) {
        $post = get_post($post);
        $meta = self::raw_meta($post->ID);
        $out  = array();

        foreach (EB_Fields::keys() as $field) {
            $value = isset($meta[$field]) ? $meta[$field] : '';

            // Ein einzelnes "-" heißt: für diese Stelle bewusst nichts ausgeben, auch keinen Standardwert.
            if ($value === self::SUPPRESS) {
                continue;
            }

            if ($value === '') {
                $value = EB_Settings::default_for($field);
            }
            if ($value === '') {
                $value = self::fallback($field, $post);
            }
            $value = apply_filters('custom_fields_rss_value', $value, $field, $post);
            if ($value !== '' && $value !== null) {
                $out[$field] = $value;
            }
        }

        return $out;
    }

    private static function fallback($field, $post) {
        switch ($field) {
            case 'nummer':
                return self::nummer($post);

            case 'link':
                return get_permalink($post);

            case 'og-image':
                $thumb = get_the_post_thumbnail_url($post, 'full');
                return $thumb ? $thumb : '';
        }

        return '';
    }

    /**
     * Eine neue GUID lässt den Importer die Stelle als neue Anzeige mit eigener Statistik führen,
     * eine bereits bekannte GUID wird ignoriert. Welches Verhalten gewünscht ist, entscheidet die Einstellung.
     */
    private static function nummer($post) {
        $nummer = 'post-' . $post->ID;

        if (EB_Settings::get('nummer_strategy', 'stable') === 'modified') {
            $nummer .= '-' . get_post_modified_time('YmdHi', true, $post);
        }

        return $nummer;
    }
}
