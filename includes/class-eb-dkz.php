<?php
/**
 * Berufssuche (DKZ) über login.empfehlungsbund.de.
 *
 * Der Endpunkt liefert kein JSON, sondern einen Rails-UJS-Schnipsel mit eingebetteter HTML-Tabelle.
 * Deshalb wird serverseitig geparst statt im Browser - das umgeht zugleich die fehlenden CORS-Header.
 */

defined('ABSPATH') || exit;

class EB_DKZ {

    const ENDPOINT = 'https://login.empfehlungsbund.de/arbeitsagentur/search';
    const CACHE_TTL = 12 * HOUR_IN_SECONDS;

    public static function init() {
        add_action('rest_api_init', array(__CLASS__, 'register_route'));
    }

    public static function register_route() {
        register_rest_route('custom-fields-rss/v1', '/beruf', array(
            'methods'             => 'GET',
            'callback'            => array(__CLASS__, 'handle'),
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            },
            'args'                => array(
                'q'    => array('required' => true, 'sanitize_callback' => 'sanitize_text_field'),
                'type' => array('default' => 'taetigkeit', 'sanitize_callback' => 'sanitize_key'),
            ),
        ));
    }

    public static function handle($request) {
        $query = trim($request->get_param('q'));
        $type  = $request->get_param('type');

        if (!in_array($type, array('taetigkeit', 'ausbildung', 'duales_studium'), true)) {
            $type = 'taetigkeit';
        }
        if ($query === '') {
            return rest_ensure_response(array());
        }

        $cache_key = 'eb_dkz_' . md5($type . '|' . mb_strtolower($query));
        $cached    = get_transient($cache_key);
        if (is_array($cached)) {
            return rest_ensure_response($cached);
        }

        $response = wp_remote_get(
            add_query_arg(array('q' => $query, 'type' => $type), self::ENDPOINT),
            array(
                'timeout' => 10,
                'headers' => array(
                    'X-Requested-With' => 'XMLHttpRequest',
                    'Accept'           => '*/*',
                ),
            )
        );

        if (is_wp_error($response)) {
            return new WP_Error('eb_dkz_unreachable', 'Berufssuche nicht erreichbar: ' . $response->get_error_message(), array('status' => 502));
        }
        if (wp_remote_retrieve_response_code($response) !== 200) {
            return new WP_Error('eb_dkz_http', 'Berufssuche antwortete mit HTTP ' . wp_remote_retrieve_response_code($response), array('status' => 502));
        }

        $results = self::parse(wp_remote_retrieve_body($response));
        set_transient($cache_key, $results, self::CACHE_TTL);

        return rest_ensure_response($results);
    }

    /** @return array<int, array{id: string, label: string}> */
    public static function parse($body) {
        $body = trim($body);

        $start = strpos($body, '.html(');
        if ($start === false) {
            return array();
        }
        $open  = strpos($body, '"', $start);
        $close = strrpos($body, '"');
        if ($open === false || $close === false || $close <= $open) {
            return array();
        }

        $html = str_replace(
            array('\\/', '\\"', '\\n', '\\t', '\\r'),
            array('/', '"', '', '', ''),
            substr($body, $open + 1, $close - $open - 1)
        );

        preg_match_all('#<tr>\s*<td>\s*(\d+)\s*</td>\s*<td>(.*?)</td>#s', $html, $matches, PREG_SET_ORDER);

        $results = array();
        foreach ($matches as $match) {
            $results[] = array(
                'id'    => $match[1],
                'label' => html_entity_decode(trim(wp_strip_all_tags($match[2])), ENT_QUOTES, 'UTF-8'),
            );
        }

        return $results;
    }
}
