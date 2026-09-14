<?php
/** Rendert ein einzelnes Feld - identisch in Meta-Box und Einstellungen. */

defined('ABSPATH') || exit;

class EB_Field_Renderer {

    public static function render($field, $config, $value, $name, $id, $placeholder_override = '') {
        $type        = isset($config['type']) ? $config['type'] : 'text';
        $placeholder = $placeholder_override !== '' ? $placeholder_override : (isset($config['placeholder']) ? $config['placeholder'] : '');

        switch ($type) {
            case 'select':
                $empty = isset($config['options'][$placeholder])
                    ? '– Standard: ' . $config['options'][$placeholder] . ' –'
                    : '– keine Angabe –';
                ?>
                <select name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($id); ?>" class="regular-text">
                    <option value=""><?php echo esc_html($empty); ?></option>
                    <?php foreach ($config['options'] as $option => $label) : ?>
                        <option value="<?php echo esc_attr($option); ?>" <?php selected($value, $option); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php
                break;

            case 'datalist':
                ?>
                <input type="text" class="regular-text"
                       name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($id); ?>"
                       value="<?php echo esc_attr($value); ?>"
                       list="<?php echo esc_attr($id); ?>-list"
                       placeholder="<?php echo esc_attr($placeholder); ?>">
                <datalist id="<?php echo esc_attr($id); ?>-list">
                    <?php foreach ($config['options'] as $option) : ?>
                        <option value="<?php echo esc_attr($option); ?>"></option>
                    <?php endforeach; ?>
                </datalist>
                <?php
                break;

            case 'checkboxes':
                $selected = array_filter(array_map('trim', explode(',', (string) $value)));
                ?>
                <fieldset id="<?php echo esc_attr($id); ?>">
                    <?php foreach ($config['options'] as $option => $label) : ?>
                        <label style="margin-right:1em;white-space:nowrap">
                            <input type="checkbox" name="<?php echo esc_attr($name); ?>[]"
                                   value="<?php echo esc_attr($option); ?>"
                                   <?php checked(in_array($option, $selected, true)); ?>>
                            <?php echo esc_html($label); ?>
                        </label>
                    <?php endforeach; ?>
                </fieldset>
                <?php
                break;

            case 'media':
                ?>
                <span class="eb-media">
                    <input type="url" class="regular-text eb-media-url"
                           name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($id); ?>"
                           value="<?php echo esc_attr($value); ?>"
                           placeholder="<?php echo esc_attr($placeholder ? $placeholder : 'https://…'); ?>">
                    <button type="button" class="button eb-media-select">Bild auswählen</button>
                    <button type="button" class="button-link eb-media-clear" <?php echo $value ? '' : 'hidden'; ?>>entfernen</button>
                    <span class="eb-media-preview"><?php if ($value) : ?>
                        <img src="<?php echo esc_url($value); ?>" alt="">
                    <?php endif; ?></span>
                </span>
                <?php
                break;

            case 'beruf':
                ?>
                <span class="eb-beruf">
                    <input type="text" class="regular-text eb-beruf-id"
                           name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($id); ?>"
                           value="<?php echo esc_attr($value); ?>"
                           placeholder="DKZ-ID, z.B. 13659">
                    <input type="search" class="regular-text eb-beruf-search"
                           placeholder="Beruf suchen, z.B. Fachinformatiker">
                    <button type="button" class="button eb-beruf-go">Suchen</button>
                    <span class="spinner"></span>
                    <div class="eb-beruf-results" hidden></div>
                </span>
                <?php
                break;

            default:
                ?>
                <input type="text" class="regular-text"
                       name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($id); ?>"
                       value="<?php echo esc_attr($value); ?>"
                       placeholder="<?php echo esc_attr($placeholder); ?>">
                <?php
        }
    }
}
