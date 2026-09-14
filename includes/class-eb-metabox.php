<?php
/** Meta-Box zur Pflege der Stellenanzeigen-Felder direkt am Beitrag. */

defined('ABSPATH') || exit;

class EB_Metabox {

    const NONCE = 'custom_fields_rss_nonce';

    public static function init() {
        add_action('add_meta_boxes', array(__CLASS__, 'add'));
        add_action('save_post', array(__CLASS__, 'save'), 10, 2);
    }

    public static function add() {
        foreach (EB_Fields::post_types() as $type) {
            add_meta_box(
                'custom-fields-rss',
                'Empfehlungsbund Stellenanzeige',
                array(__CLASS__, 'render'),
                $type,
                'normal',
                'high'
            );
        }
    }

    public static function render($post) {
        wp_nonce_field('custom_fields_rss_save', self::NONCE);

        $schema = EB_Fields::all();
        $meta   = EB_Values::raw_meta($post->ID);
        $groups = EB_Fields::groups();
        ?>
        <p class="description">
            Leere Felder werden aus den <a href="<?php echo esc_url(admin_url('options-general.php?page=custom-fields-rss')); ?>">globalen
            Standardwerten</a> ergänzt. Der Platzhalter zeigt, was dann im Feed landet.
            Ein einzelnes <code>-</code> lässt das Feld für diese Stelle bewusst weg.
        </p>
        <?php
        foreach ($groups as $group => $title) {
            $fields = array_filter($schema, function ($config) use ($group) {
                return (isset($config['group']) ? $config['group'] : 'optional') === $group;
            });
            if (!$fields) {
                continue;
            }

            // Der veraltete Block bleibt verborgen, solange ihn niemand nutzt.
            $has_value = (bool) array_intersect_key($meta, $fields);
            if ($group === 'veraltet' && !$has_value) {
                continue;
            }
            ?>
            <h3 class="eb-group"><?php echo esc_html($title); ?></h3>
            <table class="form-table eb-table" role="presentation">
                <?php
                foreach ($fields as $field => $config) {
                    $value    = isset($meta[$field]) ? $meta[$field] : '';
                    $inherited = EB_Settings::default_for($field);
                    if ($inherited === '' && $field === 'link') {
                        $inherited = get_permalink($post);
                    }
                    if ($inherited === '' && $field === 'nummer') {
                        $inherited = 'automatisch';
                    }
                    if ($inherited === '' && $field === 'og-image') {
                        $inherited = (string) get_the_post_thumbnail_url($post, 'full');
                    }
                    ?>
                    <tr>
                        <th scope="row">
                            <label for="eb-<?php echo esc_attr($field); ?>"><?php echo esc_html($config['label']); ?></label>
                            <code class="eb-key">eb:<?php echo esc_html($field); ?></code>
                        </th>
                        <td>
                            <?php
                            EB_Field_Renderer::render(
                                $field,
                                $config,
                                $value,
                                'eb[' . $field . ']',
                                'eb-' . $field,
                                $inherited
                            );
                            ?>
                            <?php if (!empty($config['help'])) : ?>
                                <p class="description"><?php echo esc_html($config['help']); ?></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <?php
        }
    }

    public static function save($post_id, $post) {
        if (!isset($_POST[self::NONCE]) || !wp_verify_nonce($_POST[self::NONCE], 'custom_fields_rss_save')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $schema = EB_Fields::all();
        $input  = isset($_POST['eb']) ? (array) wp_unslash($_POST['eb']) : array();

        foreach ($schema as $field => $config) {
            $value = isset($input[$field]) ? $input[$field] : '';
            $value = is_array($value)
                ? implode(',', array_map('sanitize_text_field', $value))
                : sanitize_text_field($value);
            $value = trim($value);

            // Alte Schreibweisen aus Custom Field Template mit aufräumen, sonst stünden zwei Werte im Feed.
            foreach (array_keys((array) get_post_custom($post_id)) as $existing) {
                if (EB_Fields::normalize_key($existing) === $field) {
                    delete_post_meta($post_id, $existing);
                }
            }

            if ($value !== '') {
                update_post_meta($post_id, $field, $value);
            }
        }
    }
}
