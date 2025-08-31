<?php
/**
 * REST API controller for S2J Slug Generater plugin
 *
 * @package S2J_Slug_Generater
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * REST Controller class
 */
class S2J_Slug_Generater_Rest_Controller {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }
    
    /**
     * Register REST routes
     */
    public function register_routes() {
        register_rest_route('s2j-slug-generater/v1', '/generate', array(
            'methods' => 'POST',
            'callback' => array($this, 'generate_slug_candidates'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'title' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => function($param) {
                        return !empty($param);
                    }
                ),
                'nonce' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ));
    }
    
    /**
     * Check user permissions
     */
    public function check_permissions($request) {
        // Check nonce
        $nonce = $request->get_param('nonce');
        if (!wp_verify_nonce($nonce, 's2j_slug_generater_nonce')) {
            return new WP_Error(
                'invalid_nonce',
                __('Security check failed.', 's2j-slug-generater'),
                array('status' => 403)
            );
        }
        
        // Check user capabilities
        if (!current_user_can('edit_posts')) {
            return new WP_Error(
                'insufficient_permissions',
                __('You do not have permission to perform this action.', 's2j-slug-generater'),
                array('status' => 403)
            );
        }
        
        return true;
    }
    
    /**
     * Generate slug candidates
     */
    public function generate_slug_candidates($request) {
        $title = $request->get_param('title');
        
        // Initialize slug generator
        $generater = new S2J_Slug_Generater_Generater();
        
        try {
            $result = $generater->generate_candidates($title);
            
            return array(
                'success' => true,
                'data' => array(
                    'candidates' => $result['candidates'],
                    'similarity' => $result['similarity'],
                    'translated_title' => $result['translated_title']
                )
            );
            
        } catch (Exception $e) {
            return new WP_Error(
                'generation_failed',
                $e->getMessage(),
                array('status' => 500)
            );
        }
    }
}
