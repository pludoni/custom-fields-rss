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
            'defaults'        => array(),
            'nummer_strategy' => 'stable',
            'post_types'      => array('post'),
        );
        return wp_parse_args((array) get_option(self::OPTION, array()), $defaults);
    }

    public static function get($key, $fallback = '') {
        $options = self::options();
        return isset($options[$key]) ? $options[$key] : $fallback;
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
            'defaults'        => array(),
            'nummer_strategy' => in_array(isset($input['nummer_strategy']) ? $input['nummer_strategy'] : '', array('stable', 'modified'), true)
                ? $input['nummer_strategy']
                : 'stable',
            'post_types'      => array(),
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
