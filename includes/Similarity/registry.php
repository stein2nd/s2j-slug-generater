<?php
/**
 * Similarity AI provider registry (data + compare adapters).
 *
 * @package S2J_Slug_Generater
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/openai.php';

/**
 * Built-in similarity AI provider descriptors.
 *
 * @return array<string, array>
 */
function s2j_sg_similarity_ai_providers() {
    static $providers = null;

    if ($providers === null) {
        $providers = array(
            'openai' => s2j_sg_similarity_ai_provider_openai(),
        );
    }

    return $providers;
}

/**
 * Lookup a similarity AI provider by id.
 *
 * @param string $id Provider id.
 * @return array{ok:true,value:array}|array{ok:false,error:array{code:string,message:string}}
 */
function s2j_sg_lookup_similarity_ai_provider($id) {
    $providers = s2j_sg_similarity_ai_providers();

    if (!isset($providers[$id])) {
        return array(
            'ok' => false,
            'error' => array(
                'code' => 'unknown_provider',
                'message' => __('Unsupported similarity AI service.', 's2j-slug-generater'),
            ),
        );
    }

    return array(
        'ok' => true,
        'value' => $providers[$id],
    );
}

/**
 * Build similarity API key help HTML from provider URLs (i18n at boundary).
 *
 * @param array $provider SimilarityAiProvider descriptor.
 * @return string
 */
function s2j_sg_format_similarity_api_key_help(array $provider) {
    return sprintf(
        /* translators: 1: signup URL, 2: billing URL, 3: API keys URL */
        __('Create an account on <a target="_blank" href="%1$s">the signup page</a>, add credits on <a target="_blank" href="%2$s">the billing page</a>, then <a target="_blank" href="%3$s">obtain an API key</a>. This key is separate from the translation API key.', 's2j-slug-generater'),
        esc_url($provider['signupUrl']),
        esc_url($provider['billingUrl']),
        esc_url($provider['keysUrl'])
    );
}

/**
 * Compare similarity via the selected provider.
 *
 * @param array  $req         SimilarityRequest-like array.
 * @param string $provider_id SimilarityAiProviderId.
 * @return array{ok:true,value:float}|array{ok:false,error:array{code:string,message:string}}
 */
function s2j_sg_compare_with_similarity_provider(array $req, $provider_id) {
    $lookup = s2j_sg_lookup_similarity_ai_provider($provider_id);
    if (!$lookup['ok']) {
        return $lookup;
    }

    $compare = $lookup['value']['compare'];
    return $compare($req);
}
