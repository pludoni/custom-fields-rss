<?php
/** Schreibt die eb:-Felder in den RSS2-Feed. */

defined('ABSPATH') || exit;

class EB_Feed {

    /** Namensraum-URI, fest verdrahtet im Importer - nicht ändern. */
    const NS = 'http://www.empfehlungsbund.de/schema.dtd';

    public static function init() {
        add_action('rss2_ns', array(__CLASS__, 'add_ns'));
        add_action('rss2_item', array(__CLASS__, 'add_item_fields'));
    }

    public static function add_ns() {
        echo 'xmlns:eb="' . esc_attr(self::NS) . '"' . "\n";
    }

    public static function add_item_fields() {
        $post = get_post();
        if (!$post || !in_array($post->post_type, EB_Fields::post_types(), true)) {
            return;
        }

        foreach (EB_Values::resolved($post) as $field => $value) {
            printf("\t\t<eb:%1\$s>%2\$s</eb:%1\$s>\n", $field, esc_html($value));
        }
    }
}
