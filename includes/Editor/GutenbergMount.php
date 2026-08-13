<?php
/**
 * Gutenberg editor mount (PluginDocumentSettingPanel via script).
 *
 * @package S2J_Slug_Generater
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Gutenberg enqueue / localize only (UI lives in src/gutenberg).
 */
class S2J_Slug_Generater_Gutenberg_Mount {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action('enqueue_block_editor_assets', array($this, 'enqueue'));
    }

    /**
     * Enqueue Gutenberg panel assets.
     */
    public function enqueue() {
        if (!function_exists('register_block_type')) {
            return;
        }

        wp_enqueue_script(
            's2j-slug-generater-gutenberg',
            S2J_SLUG_GENERATER_PLUGIN_URL . 'dist/js/s2j-slug-generater-gutenberg.js',
            array(
                'wp-plugins',
                'wp-edit-post',
                'wp-editor',
                'wp-element',
                'wp-components',
                'wp-i18n',
                'wp-data',
                'wp-api-fetch',
            ),
            S2J_SLUG_GENERATER_VERSION,
            true
        );

        wp_enqueue_style(
            's2j-slug-generater-gutenberg',
            S2J_SLUG_GENERATER_PLUGIN_URL . 'dist/css/s2j-slug-generater-gutenberg.css',
            array('wp-components'),
            S2J_SLUG_GENERATER_VERSION
        );

        wp_set_script_translations(
            's2j-slug-generater-gutenberg',
            's2j-slug-generater',
            S2J_SLUG_GENERATER_PLUGIN_DIR . 'languages'
        );

        wp_localize_script(
            's2j-slug-generater-gutenberg',
            's2jSlugGeneraterData',
            array(
                'restUrl' => esc_url_raw(rest_url('s2j-slug-generater/v1/generate')),
                'nonce' => wp_create_nonce('wp_rest'),
                'version' => S2J_SLUG_GENERATER_VERSION,
            )
        );
    }
}
