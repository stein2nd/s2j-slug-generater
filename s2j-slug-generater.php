<?php
/**
 * S2J Slug Generater
 *
 * @package S2J_Slug_Generater
 * @author Koutarou ISHIKAWA
 * @copyright 2025 Koutarou ISHIKAWA
 * @license GPL v3 or later

 * Plugin Name: S2J Slug Generater
 * Plugin URI: https://github.com/stein2nd/s2j-slug-generater
 * Description: Generate optimal slug candidates using translation service APIs. Supports both Gutenberg block editor and Classic editor.
 * Version: 2.0.9
 * Author: Koutarou ISHIKAWA
 * Author URI: https://stein2nd.wordpress.com
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: s2j-slug-generater
 * Domain Path: /languages
 * Requires at least: 6.3
 * Tested up to: 6.8
 * Requires PHP: 8.2
 * Network: false
 */

if (!defined('ABSPATH')) {
    exit;
}

define('S2J_SLUG_GENERATER_VERSION', '2.0.9');
define('S2J_SLUG_GENERATER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('S2J_SLUG_GENERATER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('S2J_SLUG_GENERATER_PLUGIN_BASENAME', plugin_basename(__FILE__));

$s2j_slug_generater_autoload = S2J_SLUG_GENERATER_PLUGIN_DIR . 'vendor/autoload.php';
if (file_exists($s2j_slug_generater_autoload)) {
    require_once $s2j_slug_generater_autoload;
}

/**
 * Load plugin PHP modules (FOP outer frame + pure domain).
 */
function s2j_slug_generater_load_dependencies() {
    $dir = S2J_SLUG_GENERATER_PLUGIN_DIR . 'includes/';

    require_once $dir . 'Domain/pure.php';
    require_once $dir . 'Config/PluginConfig.php';
    require_once $dir . 'Providers/registry.php';
    require_once $dir . 'Similarity/registry.php';
    require_once $dir . 'Pipeline/generate_candidate.php';
    require_once $dir . 'RestController.php';
    require_once $dir . 'Admin/SettingsPage.php';
    require_once $dir . 'Editor/GutenbergMount.php';
    require_once $dir . 'Editor/ClassicMount.php';
}

/**
 * Bootstrap plugin components.
 */
function s2j_slug_generater_bootstrap() {
    s2j_slug_generater_load_dependencies();
    S2J_Slug_Generater_Plugin_Config::maybe_migrate_threshold();

    new S2J_Slug_Generater_Settings_Page();
    new S2J_Slug_Generater_Rest_Controller();
    new S2J_Slug_Generater_Gutenberg_Mount();
    new S2J_Slug_Generater_Classic_Mount();
}

/**
 * Load text domain.
 */
function s2j_slug_generater_load_textdomain() {
    load_plugin_textdomain(
        's2j-slug-generater',
        false,
        dirname(S2J_SLUG_GENERATER_PLUGIN_BASENAME) . '/languages'
    );
}

/**
 * Enqueue admin settings assets (optional React shell; PHP Settings API is primary).
 *
 * @param string $hook Hook suffix.
 */
function s2j_slug_generater_enqueue_admin_scripts($hook) {
    if ('settings_page_s2j-slug-generater' !== $hook) {
        return;
    }

    $admin_js = S2J_SLUG_GENERATER_PLUGIN_DIR . 'dist/js/s2j-slug-generater-admin.js';
    $admin_css = S2J_SLUG_GENERATER_PLUGIN_DIR . 'dist/css/s2j-slug-generater-admin.css';

    if (file_exists($admin_css)) {
        wp_enqueue_style(
            's2j-slug-generater-admin',
            S2J_SLUG_GENERATER_PLUGIN_URL . 'dist/css/s2j-slug-generater-admin.css',
            array(),
            S2J_SLUG_GENERATER_VERSION
        );
    }

    // PHP Settings API owns the form; admin JS is reserved for progressive enhancement.
    if (file_exists($admin_js)) {
        wp_enqueue_script(
            's2j-slug-generater-admin',
            S2J_SLUG_GENERATER_PLUGIN_URL . 'dist/js/s2j-slug-generater-admin.js',
            array('jquery'),
            S2J_SLUG_GENERATER_VERSION,
            true
        );
    }
}

add_action('plugins_loaded', 's2j_slug_generater_load_textdomain');
add_action('init', 's2j_slug_generater_bootstrap');
add_action('admin_enqueue_scripts', 's2j_slug_generater_enqueue_admin_scripts');
