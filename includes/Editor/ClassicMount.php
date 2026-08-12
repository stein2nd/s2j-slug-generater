<?php
/**
 * Classic editor mount (UI below post title).
 *
 * @package S2J_Slug_Generater
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classic editor: panel after title + assets.
 */
class S2J_Slug_Generater_Classic_Mount {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action('edit_form_after_title', array($this, 'render_panel'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue'));
    }

    /**
     * Whether the current screen uses the classic editor.
     *
     * @return bool
     */
    private function is_classic_edit_screen() {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || $screen->base !== 'post') {
            return false;
        }

        $post = get_post();
        if ($post && function_exists('use_block_editor_for_post')) {
            return !use_block_editor_for_post($post);
        }

        if (!empty($screen->post_type) && function_exists('use_block_editor_for_post_type')) {
            return !use_block_editor_for_post_type($screen->post_type);
        }

        return true;
    }

    /**
     * Render UI directly under the title field.
     *
     * @param WP_Post $post Post.
     */
    public function render_panel($post) {
        if (!$this->is_classic_edit_screen()) {
            return;
        }

        echo '<div id="s2j-slug-generater-classic" class="s2j-slug-generater-classic">';
        echo '<h2>' . esc_html__('S2J Slug Generater', 's2j-slug-generater') . '</h2>';
        echo '<p><button type="button" id="s2j-generate-candidates" class="button">' . esc_html__('Generate Candidates', 's2j-slug-generater') . '</button></p>';
        echo '<p><label for="s2j-slug-candidate">' . esc_html__('Slug Candidate:', 's2j-slug-generater') . '</label><br>';
        echo '<input type="text" id="s2j-slug-candidate" class="regular-text" value="" /></p>';
        echo '<p><label>' . esc_html__('Similarity:', 's2j-slug-generater') . '</label> <span id="s2j-similarity-value">-</span>%</p>';
        echo '<p><button type="button" id="s2j-slugify" class="button button-primary" disabled>' . esc_html__('Slugify', 's2j-slug-generater') . '</button></p>';
        echo '<div id="s2j-classic-messages"></div>';
        echo '</div>';
    }

    /**
     * Enqueue classic assets on post screens.
     *
     * @param string $hook Hook suffix.
     */
    public function enqueue($hook) {
        if (!in_array($hook, array('post.php', 'post-new.php'), true)) {
            return;
        }

        if (!$this->is_classic_edit_screen()) {
            return;
        }

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

        wp_localize_script(
            's2j-slug-generater-classic',
            's2jSlugGeneraterData',
            array(
                'restUrl' => esc_url_raw(rest_url('s2j-slug-generater/v1/generate')),
                'nonce' => wp_create_nonce('wp_rest'),
                'version' => S2J_SLUG_GENERATER_VERSION,
                'i18n' => array(
                    'emptyTitle' => __('Please enter a post title first.', 's2j-slug-generater'),
                    'generating' => __('Generating...', 's2j-slug-generater'),
                    'generate' => __('Generate Candidates', 's2j-slug-generater'),
                    'success' => __('Slug candidate generated successfully.', 's2j-slug-generater'),
                    'failed' => __('Failed to generate slug candidate.', 's2j-slug-generater'),
                    'error' => __('An error occurred while generating the slug candidate.', 's2j-slug-generater'),
                    'emptyCandidate' => __('Please generate a candidate first.', 's2j-slug-generater'),
                    'applied' => __('Slug applied successfully.', 's2j-slug-generater'),
                    'notAccepted' => __('Similarity is below the threshold. Slugify is disabled.', 's2j-slug-generater'),
                ),
            )
        );
    }
}
