<?php
/**
 * Slug generator class for S2J Slug Generater plugin
 *
 * @package S2J_Slug_Generater
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main slug generator class
 */
class S2J_Slug_Generater_Generater {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Initialize Gutenberg block
        add_action('init', array($this, 'init_gutenberg_block'));
        
        // Initialize Classic editor metabox
        add_action('add_meta_boxes', array($this, 'add_metabox'));
        add_action('save_post', array($this, 'save_metabox_data'));
    }
    
    /**
     * Initialize Gutenberg block
     */
    public function init_gutenberg_block() {
        if (!function_exists('register_block_type')) {
            return;
        }
        
        register_block_type('s2j-slug-generater/slug-generater', array(
            'editor_script' => 's2j-slug-generater-gutenberg',
            'editor_style' => 's2j-slug-generater-gutenberg',
            'render_callback' => array($this, 'render_gutenberg_block')
        ));
    }
    
    /**
     * Add metabox for Classic editor
     */
    public function add_metabox() {
        $post_types = get_post_types(array('public' => true));
        
        foreach ($post_types as $post_type) {
            add_meta_box(
                's2j_slug_generater_metabox',
                __('S2J Slug Generater', 's2j-slug-generater'),
                array($this, 'render_metabox'),
                $post_type,
                'normal',
                'high'
            );
        }
    }
    
    /**
     * Save metabox data
     */
    public function save_metabox_data($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        if (isset($_POST['s2j_slug_generater_nonce']) && 
            wp_verify_nonce($_POST['s2j_slug_generater_nonce'], 's2j_slug_generater_save')) {
            
            if (isset($_POST['s2j_slug_generater_slug'])) {
                $slug = sanitize_title($_POST['s2j_slug_generater_slug']);
                update_post_meta($post_id, '_s2j_slug_generater_slug', $slug);
            }
        }
    }
    
    /**
     * Render Gutenberg block
     */
    public function render_gutenberg_block() {
        // This will be handled by React component
        return '<div id="s2j-slug-generater-gutenberg-block"></div>';
    }
    
    /**
     * Render Classic editor metabox
     */
    public function render_metabox($post) {
        wp_nonce_field('s2j_slug_generater_save', 's2j_slug_generater_nonce');
        
        $current_slug = get_post_meta($post->ID, '_s2j_slug_generater_slug', true);
        
        echo '<div id="s2j-slug-generater-classic-metabox">';
        echo '<p><strong>' . __('Generate slug candidates using translation API:', 's2j-slug-generater') . '</strong></p>';
        echo '<p><button type="button" id="s2j-generate-candidates" class="button">' . __('Generate Candidates', 's2j-slug-generater') . '</button></p>';
        echo '<p><label for="s2j-slug-candidates">' . __('Slug Candidates:', 's2j-slug-generater') . '</label><br>';
        echo '<input type="text" id="s2j-slug-candidates" name="s2j_slug_generater_slug" value="' . esc_attr($current_slug) . '" class="regular-text" /></p>';
        echo '<p><label for="s2j-similarity">' . __('Similarity:', 's2j-slug-generater') . '</label> <span id="s2j-similarity-value">-</span>%</p>';
        echo '<p><button type="button" id="s2j-slugify" class="button button-primary">' . __('Slugify', 's2j-slug-generater') . '</button></p>';
        echo '</div>';
    }
    
    /**
     * Generate slug candidates using translation API
     */
    public function generate_candidates($title) {
        // Get plugin settings
        $translation_service = get_option('s2j_slug_generater_translation_service', 'deepl');
        $api_key = get_option('s2j_slug_generater_api_key', '');
        $source_language = get_option('s2j_slug_generater_source_language', 'ja');
        $similarity_threshold = get_option('s2j_slug_generater_similarity_threshold', 80);
        
        if (empty($api_key)) {
            throw new Exception(__('API key is not configured.', 's2j-slug-generater'));
        }
        
        // Create translation service instance
        $service = $this->create_translation_service($translation_service, $api_key);
        
        // Step 1: Translate title to English
        $translated_title = $service->translate($title, $source_language, 'en');
        
        // Step 2: Reverse translate to source language
        $reverse_translated = $service->translate($translated_title, 'en', $source_language);
        
        // Step 3: Calculate similarity
        $similarity = $this->calculate_similarity($title, $reverse_translated);
        
        // Step 4: Generate slug candidates
        $candidates = $this->generate_slug_candidates_from_text($translated_title);
        
        // Filter candidates based on similarity threshold
        $filtered_candidates = array();
        foreach ($candidates as $candidate) {
            if ($similarity >= $similarity_threshold) {
                $filtered_candidates[] = $candidate;
            }
        }
        
        return array(
            'candidates' => $filtered_candidates,
            'similarity' => round($similarity, 2),
            'translated_title' => $translated_title
        );
    }
    
    /**
     * Create translation service instance
     */
    private function create_translation_service($service_type, $api_key) {
        switch ($service_type) {
            case 'deepl':
                return new S2J_DeepL_Translation_Service($api_key);
            case 'google':
                return new S2J_Google_Translation_Service($api_key);
            default:
                throw new Exception(__('Unsupported translation service.', 's2j-slug-generater'));
        }
    }
    
    /**
     * Calculate similarity between two strings using Levenshtein distance
     */
    private function calculate_similarity($string1, $string2) {
        $lev_distance = levenshtein($string1, $string2);
        $max_length = max(strlen($string1), strlen($string2));
        
        if ($max_length === 0) {
            return 100;
        }
        
        return (1 - ($lev_distance / $max_length)) * 100;
    }
    
    /**
     * Generate slug candidates from text
     */
    private function generate_slug_candidates_from_text($text) {
        $candidates = array();
        
        // Convert to lowercase and remove special characters
        $clean_text = preg_replace('/[^a-zA-Z0-9\s]/', '', strtolower($text));
        
        // Split into words
        $words = preg_split('/\s+/', trim($clean_text));
        
        // Generate different combinations
        if (count($words) >= 1) {
            $candidates[] = implode('-', $words);
        }
        
        if (count($words) >= 2) {
            $candidates[] = implode('-', array_slice($words, 0, 2));
            $candidates[] = implode('-', array_slice($words, -2));
        }
        
        if (count($words) >= 3) {
            $candidates[] = implode('-', array_slice($words, 0, 3));
        }
        
        // Add first word as candidate
        if (!empty($words[0])) {
            $candidates[] = $words[0];
        }
        
        return array_unique($candidates);
    }
}

/**
 * Abstract translation service class
 */
abstract class S2J_Translation_Service {
    protected $api_key;
    
    public function __construct($api_key) {
        $this->api_key = $api_key;
    }
    
    abstract public function translate($text, $source_lang, $target_lang);
}

/**
 * DeepL translation service
 */
class S2J_DeepL_Translation_Service extends S2J_Translation_Service {
    
    public function translate($text, $source_lang, $target_lang) {
        $url = 'https://api-free.deepl.com/v2/translate';
        
        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'DeepL-Auth-Key ' . $this->api_key,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ),
            'body' => array(
                'text' => $text,
                'source_lang' => strtoupper($source_lang),
                'target_lang' => strtoupper($target_lang)
            )
        ));
        
        if (is_wp_error($response)) {
            throw new Exception(__('Translation request failed: ', 's2j-slug-generater') . $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['translations'][0]['text'])) {
            return $data['translations'][0]['text'];
        }
        
        throw new Exception(__('Translation failed: Invalid response from DeepL API.', 's2j-slug-generater'));
    }
}

/**
 * Google translation service
 */
class S2J_Google_Translation_Service extends S2J_Translation_Service {
    
    public function translate($text, $source_lang, $target_lang) {
        $url = 'https://translation.googleapis.com/language/translate/v2';
        
        $response = wp_remote_post($url, array(
            'body' => array(
                'key' => $this->api_key,
                'q' => $text,
                'source' => $source_lang,
                'target' => $target_lang
            )
        ));
        
        if (is_wp_error($response)) {
            throw new Exception(__('Translation request failed: ', 's2j-slug-generater') . $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['data']['translations'][0]['translatedText'])) {
            return $data['data']['translations'][0]['translatedText'];
        }
        
        throw new Exception(__('Translation failed: Invalid response from Google Translate API.', 's2j-slug-generater'));
    }
}
