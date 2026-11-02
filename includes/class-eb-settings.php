<?php
/** Globale Standardwerte, damit pro Stelle nur noch das Abweichende gepflegt werden muss. */

defined('ABSPATH') || exit;

class EB_Settings {

    const OPTION = 'custom_fields_rss_options';

    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_page'));
        add_action('admin_init', array(__CLASS__, 'register'));
        add_filter('plugin_action_links_' . plugin_basename(CUSTOM_FIELDS_RSS_FILE), array(__CLASS__, 'action_links'));
    }

    public static function options() {
        $defaults = array(
            'defaults'          => array(),
            'nummer_strategy'   => 'stable',
            'post_types'        => array('post'),
            'bms_username'      => '',
            'apply_button'      => false,
            'apply_button_text' => '',
            'feed_fields'       => array(),
        );
        return wp_parse_args((array) get_option(self::OPTION, array()), $defaults);
    }

    public static function get($key, $fallback = '') {
        $options = self::options();
        return isset($options[$key]) ? $options[$key] : $fallback;
    }

    /** @return array<int, array{key: string, heading: string}> */
    public static function feed_fields() {
        return array_values(array_filter((array) self::get('feed_fields', array()), function ($field) {
            return is_array($field) && !empty($field['key']);
        }));
    }

    public static function default_for($field) {
        $defaults = (array) self::get('defaults', array());
        return isset($defaults[$field]) ? trim((string) $defaults[$field]) : '';
    }

    public static function action_links($links) {
        array_unshift($links, sprintf(
            '<a href="%s">Einstellungen</a>',
            esc_url(admin_url('options-general.php?page=custom-fields-rss'))
        ));
        return $links;
    }

    public static function add_page() {
        add_options_page(
            'Empfehlungsbund',
            'Empfehlungsbund',
            'manage_options',
            'custom-fields-rss',
            array(__CLASS__, 'render')
        );
    }

    public static function register() {
        register_setting('custom_fields_rss', self::OPTION, array(
            'sanitize_callback' => array(__CLASS__, 'sanitize'),
        ));
    }

    public static function sanitize($input) {
        $clean = array(
            'defaults'          => array(),
            'nummer_strategy'   => in_array(isset($input['nummer_strategy']) ? $input['nummer_strategy'] : '', array('stable', 'modified'), true)
                ? $input['nummer_strategy']
                : 'stable',
            'post_types'        => array(),
            'bms_username'      => self::sanitize_username(isset($input['bms_username']) ? $input['bms_username'] : ''),
            'apply_button'      => !empty($input['apply_button']),
            'apply_button_text' => sanitize_text_field(isset($input['apply_button_text']) ? $input['apply_button_text'] : ''),
            'feed_fields'       => self::parse_feed_fields(isset($input['feed_fields']) ? $input['feed_fields'] : ''),
        );

        $schema = EB_Fields::all();
        foreach ((array) (isset($input['defaults']) ? $input['defaults'] : array()) as $field => $value) {
            if (!isset($schema[$field]) || empty($schema[$field]['global'])) {
                continue;
            }
            $value = is_array($value) ? implode(',', array_map('sanitize_text_field', $value)) : sanitize_text_field($value);
            if (trim($value) !== '') {
                $clean['defaults'][$field] = trim($value);
            }
        }

        foreach ((array) (isset($input['post_types']) ? $input['post_types'] : array()) as $type) {
            if (post_type_exists($type)) {
                $clean['post_types'][] = sanitize_key($type);
            }
        }
        if (empty($clean['post_types'])) {
            $clean['post_types'] = array('post');
        }

        return $clean;
    }

    /** Eine Zeile je Feld: "meta_key" oder "meta_key | Überschrift". */
    private static function parse_feed_fields($text) {
        if (is_array($text)) {
            return $text;
        }
        $fields = array();
        foreach (preg_split('/\R/', (string) $text) as $line) {
            $parts = array_map('trim', explode('|', $line, 2));
            $key   = preg_replace('/[^A-Za-z0-9_-]/', '', $parts[0]);
            if ($key !== '') {
                $fields[] = array('key' => $key, 'heading' => sanitize_text_field(isset($parts[1]) ? $parts[1] : ''));
            }
        }
        return $fields;
    }

    /** Ungültige Eingaben verwerfen statt still zu kürzen: aus "max@firma.de" würde sonst "maxfirma.de". */
    private static function sanitize_username($value) {
        $value = strtolower(trim((string) $value));
        // Entspricht der Routen-Einschränkung im EBMS.
        if ($value === '' || preg_match('/^[a-z0-9._-]+$/', $value)) {
            return $value;
        }

        $message = strpos($value, '@') !== false
            ? 'Beim EBMS-Username bitte keine E-Mail-Adresse eintragen, sondern Ihren Firmennutzernamen im Empfehlungsbund (z. B. firma-gmbh). Der bisherige Wert wurde beibehalten.'
            : 'Der EBMS-Username darf nur Buchstaben, Ziffern, Punkt, Binde- und Unterstrich enthalten. Der bisherige Wert wurde beibehalten.';
        add_settings_error(self::OPTION, 'bms_username', $message);

        return self::get('bms_username', '');
    }

    public static function render() {
        $options  = self::options();
        $schema   = EB_Fields::all();
        $defaults = (array) $options['defaults'];
        ?>
        <div class="wrap">
            <h1>Empfehlungsbund Feederweiterung</h1>
            <p>
                Hier hinterlegte Werte gelten für alle Stellenanzeigen. Ein abweichender Wert direkt an der
                Stellenanzeige hat immer Vorrang. Prüfen Sie Ihren Feed anschließend unter
                <a href="https://login.empfehlungsbund.de/validator" target="_blank" rel="noopener">login.empfehlungsbund.de/validator</a>.
            </p>
            <form method="post" action="options.php">
                <?php settings_fields('custom_fields_rss'); ?>

                <h2>Beitragstypen</h2>
                <p class="description">Für welche Beitragstypen sollen die Felder gepflegt und ausgegeben werden?</p>
                <fieldset>
                    <?php foreach (get_post_types(array('public' => true), 'objects') as $type) : ?>
                        <label style="margin-right:1em">
                            <input type="checkbox"
                                   name="<?php echo esc_attr(self::OPTION); ?>[post_types][]"
                                   value="<?php echo esc_attr($type->name); ?>"
                                   <?php checked(in_array($type->name, (array) $options['post_types'], true)); ?>>
                            <?php echo esc_html($type->labels->singular_name); ?>
                        </label>
                    <?php endforeach; ?>
                </fieldset>

                <h2>Stellentext im Feed</h2>
                <p class="description">
                    Nur nötig, wenn der Stellentext nicht im normalen Beitragsinhalt steht, sondern in eigenen Feldern,
                    die z.&nbsp;B. eine Elementor-Vorlage auf der Seite zusammensetzt. Dann enthält der Feed sonst nur einen
                    Teil des Textes. Die hier genannten Felder werden in dieser Reihenfolge an den Stellentext im Feed angehängt.
                </p>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="eb-feed-fields">Felder</label></th>
                        <td>
                            <textarea class="large-text code" rows="5" id="eb-feed-fields"
                                      name="<?php echo esc_attr(self::OPTION); ?>[feed_fields]"
                                      placeholder="einleitung&#10;profil | Dein Profil&#10;aufgaben | Deine Aufgaben"><?php
                                echo esc_textarea(implode("\n", array_map(function ($field) {
                                    return $field['heading'] === '' ? $field['key'] : $field['key'] . ' | ' . $field['heading'];
                                }, self::feed_fields())));
                            ?></textarea>
                            <p class="description">Ein Feldname pro Zeile, optional mit Überschrift nach einem <code>|</code>.</p>
                            <?php $available = EB_Feed_Content::available_keys(); ?>
                            <?php if ($available) : ?>
                                <p class="description">
                                    Bei Ihren Stellen gefundene Felder:
                                    <?php echo implode(', ', array_map(function ($key) {
                                        return '<code>' . esc_html($key) . '</code>';
                                    }, $available)); ?>
                                </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>

                <h2>Automatische Stellennummer</h2>
                <p class="description">Gilt nur für Stellen, bei denen das Feld „Nummer / GUID“ leer bleibt.</p>
                <fieldset>
                    <label style="display:block;margin-bottom:.4em">
                        <input type="radio" name="<?php echo esc_attr(self::OPTION); ?>[nummer_strategy]" value="stable"
                               <?php checked($options['nummer_strategy'], 'stable'); ?>>
                        Gleich bleibend je Beitrag – eine Stelle bleibt im Portal dieselbe Anzeige.
                    </label>
                    <label style="display:block">
                        <input type="radio" name="<?php echo esc_attr(self::OPTION); ?>[nummer_strategy]" value="modified"
                               <?php checked($options['nummer_strategy'], 'modified'); ?>>
                        Neu bei jeder Änderung – jede Überarbeitung wird als neue Anzeige mit eigener Statistik geführt.
                    </label>
                </fieldset>

                <h2>Bewerben-Button</h2>
                <p class="description">
                    Verlinkt jede Stelle direkt auf ihr Bewerbungsformular im Empfehlungsbund-Bewerbermanagement (EBMS).
                    Das EBMS erkennt die Stelle an ihrer Nummer.
                </p>
                <div class="notice notice-warning inline">
                    <p>
                        <strong>Der Bewerbungsweg funktioniert erst nach dem Import beim Empfehlungsbund.</strong>
                        Eine neue Stelle ist im EBMS erst vorhanden, wenn der Empfehlungsbund Ihren Feed das nächste Mal
                        importiert hat. Bis dahin führt der Link zur Initiativbewerbung statt zur Stelle.
                    </p>
                </div>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="eb-bms-username">EBMS-Username</label></th>
                        <td>
                            <input type="text" class="regular-text" id="eb-bms-username"
                                   name="<?php echo esc_attr(self::OPTION); ?>[bms_username]"
                                   value="<?php echo esc_attr($options['bms_username']); ?>"
                                   placeholder="firma-gmbh"
                                   pattern="[A-Za-z0-9._\-]+"
                                   title="Firmennutzername ohne @, z. B. firma-gmbh">
                            <p class="description">
                                Ihr Firmennutzername im Empfehlungsbund, z.&nbsp;B. <code>firma-gmbh</code> – keine E-Mail-Adresse.
                                Falls Sie unsicher sind, fragen Sie Ihren Ansprechpartner beim Empfehlungsbund.
                                Ohne Username wird kein Link ausgegeben.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Automatisch anzeigen</th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr(self::OPTION); ?>[apply_button]" value="1"
                                       <?php checked($options['apply_button']); ?>>
                                Bewerben-Button unter dem Stellentext anzeigen
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eb-apply-button-text">Button-Text</label></th>
                        <td>
                            <input type="text" class="regular-text" id="eb-apply-button-text"
                                   name="<?php echo esc_attr(self::OPTION); ?>[apply_button_text]"
                                   value="<?php echo esc_attr($options['apply_button_text']); ?>"
                                   placeholder="Jetzt bewerben">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Shortcodes</th>
                        <td>
                            <p><code>[eb_bewerben_button text="Jetzt bewerben"]</code> – fertiger Link mit der CSS-Klasse <code>eb-bewerben-button</code>.</p>
                            <p><code>[eb_bewerben_url]</code> – nur die Adresse, z.&nbsp;B. für den Link eines Buttons im Page-Builder
                                (Elementor: Link → Dynamische Tags → „Shortcode“).</p>
                        </td>
                    </tr>
                </table>

                <h2>Standardwerte</h2>
                <table class="form-table" role="presentation">
                    <?php
                    foreach ($schema as $field => $config) {
                        if (empty($config['global'])) {
                            continue;
                        }
                        $value = isset($defaults[$field]) ? $defaults[$field] : '';
                        $name  = self::OPTION . '[defaults][' . $field . ']';
                        ?>
                        <tr>
                            <th scope="row"><label for="eb-default-<?php echo esc_attr($field); ?>"><?php echo esc_html($config['label']); ?></label></th>
                            <td>
                                <?php EB_Field_Renderer::render($field, $config, $value, $name, 'eb-default-' . $field); ?>
                                <?php if (!empty($config['help'])) : ?>
                                    <p class="description"><?php echo esc_html($config['help']); ?></p>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
