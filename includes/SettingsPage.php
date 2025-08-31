<?php
/**
 * Settings page for S2J Slug Generater plugin
 *
 * @package S2J_Slug_Generater
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings page class
 */
class S2J_Slug_Generater_Settings_Page {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'init_settings'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            __('S2J Slug Generater Settings', 's2j-slug-generater'),
            __('S2J Slug Generater', 's2j-slug-generater'),
            'manage_options',
            's2j-slug-generater',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Initialize settings
     */
    public function init_settings() {
        register_setting('s2j_slug_generater_options', 's2j_slug_generater_translation_service');
        register_setting('s2j_slug_generater_options', 's2j_slug_generater_api_key');
        register_setting('s2j_slug_generater_options', 's2j_slug_generater_source_language');
        register_setting('s2j_slug_generater_options', 's2j_slug_generater_similarity_threshold');
        
        add_settings_section(
            's2j_slug_generater_main_section',
            __('Translation Service Settings', 's2j-slug-generater'),
            array($this, 'render_section_description'),
            's2j-slug-generater'
        );
        
        add_settings_field(
            's2j_slug_generater_translation_service',
            __('Translation Service', 's2j-slug-generater'),
            array($this, 'render_translation_service_field'),
            's2j-slug-generater',
            's2j_slug_generater_main_section'
        );
        
        add_settings_field(
            's2j_slug_generater_api_key',
            __('API Key', 's2j-slug-generater'),
            array($this, 'render_api_key_field'),
            's2j-slug-generater',
            's2j_slug_generater_main_section'
        );
        
        add_settings_field(
            's2j_slug_generater_source_language',
            __('Source Language', 's2j-slug-generater'),
            array($this, 'render_source_language_field'),
            's2j-slug-generater',
            's2j_slug_generater_main_section'
        );
        
        add_settings_field(
            's2j_slug_generater_similarity_threshold',
            __('Similarity Threshold', 's2j-slug-generater'),
            array($this, 'render_similarity_threshold_field'),
            's2j-slug-generater',
            's2j_slug_generater_main_section'
        );
    }
    
    /**
     * Render section description
     */
    public function render_section_description() {
        echo '<p>' . __('Configure the translation service API settings for slug generation.', 's2j-slug-generater') . '</p>';
    }
    
    /**
     * Render translation service field
     */
    public function render_translation_service_field() {
        $current_service = get_option('s2j_slug_generater_translation_service', 'deepl');
        
        echo '<select name="s2j_slug_generater_translation_service" id="s2j_slug_generater_translation_service">';
        echo '<option value="deepl" ' . selected($current_service, 'deepl', false) . '>' . __('DeepL API', 's2j-slug-generater') . '</option>';
        echo '<option value="google" ' . selected($current_service, 'google', false) . '>' . __('Google Translate API', 's2j-slug-generater') . '</option>';
        echo '</select>';
        
        echo '<p class="description">';
        echo sprintf(
            __('Go to <a target="_blank" href="%1$s">the API plan selection page</a> and <a target="_blank" href="%2$s">obtain a free API key</a>.', 's2j-slug-generater'),
            $this->get_plan_selection_url($current_service),
            $this->get_api_key_url($current_service)
        );
        echo '</p>';
    }
    
    /**
     * Render API key field
     */
    public function render_api_key_field() {
        $api_key = get_option('s2j_slug_generater_api_key', '');
        echo '<input type="text" name="s2j_slug_generater_api_key" id="s2j_slug_generater_api_key" value="' . esc_attr($api_key) . '" class="regular-text" />';
    }
    
    /**
     * Render source language field
     */
    public function render_source_language_field() {
        $current_language = get_option('s2j_slug_generater_source_language', get_locale());
        
        // Load translation functions if not already loaded
        if (!function_exists('wp_dropdown_languages')) {
            require_once ABSPATH . 'wp-admin/includes/translation-install.php';
        }
        
        wp_dropdown_languages(array(
            'languages' => get_available_languages(),
            'translations' => wp_get_available_translations(),
            'selected' => $current_language,
            'show_available_translations' => current_user_can('install_languages') && wp_can_install_language_pack(),
            'name' => 's2j_slug_generater_source_language',
            'id' => 's2j_slug_generater_source_language'
        ));
    }
    
    /**
     * Render similarity threshold field
     */
    public function render_similarity_threshold_field() {
        $threshold = get_option('s2j_slug_generater_similarity_threshold', 80);
        echo '<input type="range" name="s2j_slug_generater_similarity_threshold" id="s2j_slug_generater_similarity_threshold" min="0" max="100" step="10" value="' . esc_attr($threshold) . '" />';
        echo '<span id="s2j_slug_generater_threshold_value">' . esc_html($threshold) . '%</span>';
        echo '<p class="description">' . __('Set the minimum similarity threshold for slug candidates.', 's2j-slug-generater') . '</p>';
    }
    
    /**
     * Get plan selection URL for the selected service
     */
    private function get_plan_selection_url($service) {
        switch ($service) {
            case 'deepl':
                return 'https://www.deepl.com/pro-api#api-pricing';
            case 'google':
                return 'https://cloud.google.com/translate/pricing';
            default:
                return '#';
        }
    }
    
    /**
     * Get API key URL for the selected service
     */
    private function get_api_key_url($service) {
        switch ($service) {
            case 'deepl':
                return 'https://www.deepl.com/ja/pro#developer';
            case 'google':
                return 'https://cloud.google.com/translate/docs/setup';
            default:
                return '#';
        }
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        echo '<div class="wrap">';
        echo '<h1>' . esc_html(get_admin_page_title()) . '</h1>';
        echo '<form action="options.php" method="post">';
        
        settings_fields('s2j_slug_generater_options');
        do_settings_sections('s2j-slug-generater');
        submit_button();
        
        echo '</form>';
        echo '</div>';
        
        // Add JavaScript for threshold slider
        echo '<script>
            jQuery(document).ready(function($) {
                $("#s2j_slug_generater_similarity_threshold").on("input", function() {
                    $("#s2j_slug_generater_threshold_value").text($(this).val() + "%");
                });
                
                $("#s2j_slug_generater_translation_service").on("change", function() {
                    var service = $(this).val();
                    var planUrl = "' . admin_url('admin-ajax.php') . '";
                    var data = {
                        action: "s2j_get_service_urls",
                        service: service,
                        nonce: "' . wp_create_nonce('s2j_service_urls_nonce') . '"
                    };
                    
                    $.post(planUrl, data, function(response) {
                        if (response.success) {
                            var description = response.data.description;
                            $("#s2j_slug_generater_translation_service").next("p.description").html(description);
                        }
                    });
                });
            });
        </script>';
    }
}
