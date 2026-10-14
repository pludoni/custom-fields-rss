<?php
/** Bewerben-Link ins Empfehlungsbund-Bewerbermanagement (EBMS), per Shortcode oder automatisch unter dem Stellentext. */

defined('ABSPATH') || exit;

class EB_Apply {

    /** Das EBMS sucht die Stelle per Feed-Nummer; unbekannte Nummern landen bei der Initiativbewerbung. */
    const BASE_URL = 'https://bms.empfehlungsbund.de/bewerbungen/start/';

    public static function init() {
        add_shortcode('eb_bewerben_url', array(__CLASS__, 'url_shortcode'));
        add_shortcode('eb_bewerben_button', array(__CLASS__, 'button_shortcode'));
        add_filter('the_content', array(__CLASS__, 'append_button'), 20);
    }

    /** Leer, wenn kein Username hinterlegt ist oder die Nummer per "-" unterdrückt wurde. */
    public static function url($post = null) {
        $post     = get_post($post);
        $username = EB_Settings::get('bms_username', '');
        if (!$post || $username === '') {
            return '';
        }

        $values = EB_Values::resolved($post);
        if (empty($values['nummer'])) {
            return '';
        }

        // Muss exakt eb:nummer im Feed entsprechen, sonst findet das EBMS die Stelle nicht.
        $url = self::BASE_URL . rawurlencode($username) . '/' . rawurlencode($values['nummer']);
        return apply_filters('custom_fields_rss_apply_url', $url, $post);
    }

    public static function button($post = null, $text = '') {
        $url = self::url($post);
        if ($url === '') {
            return '';
        }
        if ($text === '') {
            $text = self::button_text();
        }
        return sprintf('<a class="eb-bewerben-button" href="%s">%s</a>', esc_url($url), esc_html($text));
    }

    public static function url_shortcode() {
        return esc_url(self::url());
    }

    public static function button_shortcode($atts) {
        $atts = shortcode_atts(array('text' => ''), $atts, 'eb_bewerben_button');
        return self::button(null, trim($atts['text']));
    }

    public static function append_button($content) {
        if (!EB_Settings::get('apply_button', false)) {
            return $content;
        }
        // Im Feed landete der Button sonst in description/content:encoded und damit im importierten Stellentext.
        if (is_feed() || !is_singular(EB_Fields::post_types()) || !in_the_loop() || !is_main_query()) {
            return $content;
        }

        $button = self::button();
        return $button === '' ? $content : $content . "\n" . '<p class="eb-bewerben">' . $button . '</p>';
    }

    private static function button_text() {
        $text = trim((string) EB_Settings::get('apply_button_text', ''));
        return $text === '' ? 'Jetzt bewerben' : $text;
    }
}
