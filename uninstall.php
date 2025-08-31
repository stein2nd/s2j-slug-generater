<?php
/**
 * Uninstall script for S2J Slug Generator plugin
 *
 * @package S2J_Slug_Generater
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('s2j_slug_generater_api_key');
delete_option('s2j_slug_generater_translation_service');
delete_option('s2j_slug_generater_source_language');
delete_option('s2j_slug_generater_similarity_threshold');

// Delete plugin transients
delete_transient('s2j_slug_generater_api_status');
