<?php
/**
 * Translation provider registry (data + translate adapters).
 *
 * @package S2J_Slug_Generater
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/deepl.php';
require_once __DIR__ . '/google.php';

/**
 * Built-in provider descriptors.
 *
 * @return array<string, array>
 */
function s2j_sg_providers() {
    static $providers = null;

    if ($providers === null) {
        $providers = array(
            'deepl' => s2j_sg_provider_deepl(),
            'google' => s2j_sg_provider_google(),
        );
    }

    return $providers;
}

/**
 * Lookup a provider by id.
 *
 * @param string $id Provider id.
 * @return array{ok:true,value:array}|array{ok:false,error:array{code:string,message:string}}
 */
function s2j_sg_lookup_provider($id) {
    $providers = s2j_sg_providers();

    if (!isset($providers[$id])) {
        return array(
            'ok' => false,
            'error' => array(
                'code' => 'unknown_provider',
                'message' => __('Unsupported translation service.', 's2j-slug-generater'),
            ),
        );
    }

    return array(
        'ok' => true,
        'value' => $providers[$id],
    );
}

/**
 * Build API key help HTML from provider URLs (i18n at boundary).
 *
 * @param array $provider Provider descriptor.
 * @return string
 */
function s2j_sg_format_api_key_help(array $provider) {
    return sprintf(
        /* translators: 1: plan URL, 2: free key URL */
        __('Go to <a target="_blank" href="%1$s">the API plan selection page</a> and <a target="_blank" href="%2$s">obtain a free API key</a>.', 's2j-slug-generater'),
        esc_url($provider['planUrl']),
        esc_url($provider['freeKeyUrl'])
    );
}

/**
 * Translate via the selected provider.
 *
 * @param array $req TranslateRequest-like array.
 * @param string $provider_id Provider id.
 * @return array{ok:true,value:string}|array{ok:false,error:array{code:string,message:string}}
 */
function s2j_sg_translate_with_provider(array $req, $provider_id) {
    $lookup = s2j_sg_lookup_provider($provider_id);
    if (!$lookup['ok']) {
        return $lookup;
    }

    $translate = $lookup['value']['translate'];
    return $translate($req);
}
