<?php
/**
 * Uninstall script for S2J Slug Generater.
 *
 * @package S2J_Slug_Generater
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$option_keys = array(
    's2j_slug_generater_translation_service',
    's2j_slug_generater_api_key',
    's2j_slug_generater_source_language',
    's2j_slug_generater_similarity_threshold',
    's2j_slug_generater_similarity_ai_api_key',
    's2j_slug_generater_similarity_ai_model',
    's2j_slug_generater_locale',
    's2j_slug_generater_deepl_api_plan',
    's2j_slug_generater_threshold_migrated_v1',
);

foreach ($option_keys as $key) {
    delete_option($key);
}

delete_transient('s2j_slug_generater_api_status');
