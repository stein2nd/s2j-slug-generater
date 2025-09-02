<?php
/**
 * Plugin Name: S2J Slug Generater
 * Plugin URI: https://github.com/stein2nd/s2j-slug-generater
 * Description: Generate optimal slug candidates using translation service APIs. Supports both Gutenberg block editor and Classic editor.
 * Version: 1.0.0
 * Author: stein2nd
 * License: GPL v2 or later
 * Text Domain: s2j-slug-generater
 * Domain Path: /languages
 *
 * @package S2J_Slug_Generater
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('S2J_SLUG_GENERATER_VERSION', '1.0.0');
define('S2J_SLUG_GENERATER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('S2J_SLUG_GENERATER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('S2J_SLUG_GENERATER_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main plugin class
 */
class S2J_Slug_Generater {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('plugins_loaded', array($this, 'load_textdomain'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load required files
        $this->load_dependencies();
        
        // Initialize components
        $this->init_components();
        
        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_gutenberg_scripts'));
        add_action('admin_footer', array($this, 'enqueue_classic_scripts'));
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        require_once S2J_SLUG_GENERATER_PLUGIN_DIR . 'includes/SettingsPage.php';
        require_once S2J_SLUG_GENERATER_PLUGIN_DIR . 'includes/RestController.php';
        require_once S2J_SLUG_GENERATER_PLUGIN_DIR . 'includes/SlugGenerater.php';
    }
    
    /**
     * Initialize plugin components
     */
    private function init_components() {
        // Initialize settings page
        new S2J_Slug_Generater_Settings_Page();
        
        // Initialize REST controller
        new S2J_Slug_Generater_Rest_Controller();
        
        // Initialize slug generater
        new S2J_Slug_Generater_Generater();
    }
    
    /**
     * Load text domain for internationalization
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            's2j-slug-generater',
            false,
            dirname(S2J_SLUG_GENERATER_PLUGIN_BASENAME) . '/languages'
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if ('settings_page_s2j-slug-generater' === $hook) {
            wp_enqueue_script(
                's2j-slug-generater-admin',
                S2J_SLUG_GENERATER_PLUGIN_URL . 'dist/js/s2j-slug-generater-admin.js',
                array('react', 'react-dom'),
                S2J_SLUG_GENERATER_VERSION,
                true
            );
            
            wp_enqueue_style(
                's2j-slug-generater-admin',
                S2J_SLUG_GENERATER_PLUGIN_URL . 'dist/css/s2j-slug-generater-admin.css',
                array(),
                S2J_SLUG_GENERATER_VERSION
            );
        }
    }
    
    /**
     * Enqueue Gutenberg scripts and styles
     */
    public function enqueue_gutenberg_scripts() {
        // Gutenbergエディターが利用可能な場合のみスクリプトを読み込み
        if (!function_exists('register_block_type')) {
            return;
        }
        
        wp_enqueue_script(
            's2j-slug-generater-gutenberg',
            S2J_SLUG_GENERATER_PLUGIN_URL . 'dist/js/s2j-slug-generater-gutenberg.js',
            array('wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-data'),
            S2J_SLUG_GENERATER_VERSION,
            true
        );
        
        wp_enqueue_style(
            's2j-slug-generater-gutenberg',
            S2J_SLUG_GENERATER_PLUGIN_URL . 'dist/css/s2j-slug-generater-gutenberg.css',
            array('wp-components'),
            S2J_SLUG_GENERATER_VERSION
        );
        
        // スクリプトにデータを渡す
        wp_localize_script('s2j-slug-generater-gutenberg', 's2jSlugGeneraterData', array(
            'apiUrl' => rest_url('s2j-slug-generater/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'version' => S2J_SLUG_GENERATER_VERSION
        ));
    }
    
    /**
     * Enqueue Classic editor scripts and styles
     */
    public function enqueue_classic_scripts() {
        global $pagenow;
        
        if (in_array($pagenow, array('post.php', 'post-new.php'))) {
            wp_enqueue_script(
                's2j-slug-generater-classic',
                S2J_SLUG_GENERATER_PLUGIN_URL . 'dist/js/s2j-slug-generater-classic.js',
                array('jquery'),
                S2J_SLUG_GENERATER_VERSION,
                true
            );
            
            wp_enqueue_style(
                's2j-slug-generater-classic',
                S2J_SLUG_GENERATER_PLUGIN_URL . 'dist/css/s2j-slug-generater-classic.css',
                array(),
                S2J_SLUG_GENERATER_VERSION
            );
        }
    }
}

// Initialize plugin
new S2J_Slug_Generater();
