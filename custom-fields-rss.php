<?php
/**
 * Plugin Name: Empfehlungsbund Feederweiterung
 * Plugin URI: https://login.empfehlungsbund.de/validator
 * Description: Pflegt die Stellenanzeigen-Felder des Empfehlungsbundes am Beitrag und ergänzt damit den RSS-Feed um die eb:-Elemente. Standardwerte und Beitragstypen unter <a href="options-general.php?page=custom-fields-rss">Einstellungen &rarr; Empfehlungsbund</a>.
 * Version: 2.2.0
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Author: pludoni GmbH
 * Author URI: https://www.pludoni.de
 * License: GPL2
 */

defined('ABSPATH') || exit;

define('CUSTOM_FIELDS_RSS_VERSION', '2.2.0');
define('CUSTOM_FIELDS_RSS_FILE', __FILE__);

foreach (array('fields', 'values', 'feed', 'field-renderer', 'metabox', 'settings', 'dkz', 'admin-assets', 'apply') as $module) {
    require_once __DIR__ . '/includes/class-eb-' . $module . '.php';
}

EB_Feed::init();
EB_Settings::init();
EB_Metabox::init();
EB_DKZ::init();
EB_Admin_Assets::init();
EB_Apply::init();
