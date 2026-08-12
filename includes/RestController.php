<?php
/**
 * REST Facade for candidate generation.
 *
 * @package S2J_Slug_Generater
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Thin REST Facade: authorize → parse → generate → JSON.
 */
class S2J_Slug_Generater_Rest_Controller {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    /**
     * Register REST routes.
     */
    public function register_routes() {
        register_rest_route(
            's2j-slug-generater/v1',
            '/generate',
            array(
                'methods' => 'POST',
                'callback' => array($this, 'handle_generate'),
                'permission_callback' => array($this, 'authorize'),
                'args' => array(
                    'title' => array(
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
            )
        );
    }

    /**
     * Authorize via X-WP-Nonce (wp_rest) + edit_posts.
     *
     * Body nonce is intentionally not used.
     *
     * @param WP_REST_Request $request Request.
     * @return true|WP_Error
     */
    public function authorize($request) {
        $nonce = $request->get_header('X-WP-Nonce');
        if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_Error(
                'invalid_nonce',
                __('Security check failed.', 's2j-slug-generater'),
                array('status' => 403)
            );
        }

        if (!current_user_can('edit_posts')) {
            return new WP_Error(
                'forbidden',
                __('You do not have permission to perform this action.', 's2j-slug-generater'),
                array('status' => 403)
            );
        }

        return true;
    }

    /**
     * Handle POST /generate.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response|WP_Error
     */
    public function handle_generate($request) {
        $title = $request->get_param('title');
        $config = S2J_Slug_Generater_Plugin_Config::load();
        $result = s2j_sg_generate_candidate($title, $config);

        if (!$result['ok']) {
            $error = $result['error'];
            return new WP_REST_Response(
                array(
                    'success' => false,
                    'error' => array(
                        'code' => $error['code'],
                        'message' => $error['message'],
                    ),
                ),
                400
            );
        }

        $success = $result['value'];

        return new WP_REST_Response(
            array(
                'success' => true,
                'data' => array(
                    'candidates' => $success['candidates'],
                    'similarity' => $success['similarity'],
                    'translated_title' => $success['translatedTitle'],
                    'accepted' => $success['accepted'],
                ),
            ),
            200
        );
    }
}
