<?php
/**
 * DeepL translation provider descriptor + Adapter.
 *
 * @package S2J_Slug_Generater
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * DeepL provider descriptor.
 *
 * @return array
 */
function s2j_sg_provider_deepl() {
    return array(
        'id' => 'deepl',
        'planUrl' => 'https://www.deepl.com/pro-api#api-pricing',
        'freeKeyUrl' => 'https://www.deepl.com/ja/pro#developer',
        'endpoint' => 'https://api.deepl.com/v2/translate',
        'languages' => array(
            'ar' => 'Arabic',
            'bg' => 'Bulgarian',
            'cs' => 'Czech',
            'da' => 'Danish',
            'de' => 'German',
            'el' => 'Greek',
            'en' => 'English',
            'en-GB' => 'English (British)',
            'en-US' => 'English (American)',
            'es' => 'Spanish',
            'et' => 'Estonian',
            'fi' => 'Finnish',
            'fr' => 'French',
            'hu' => 'Hungarian',
            'id' => 'Indonesian',
            'it' => 'Italian',
            'ja' => 'Japanese',
            'ko' => 'Korean',
            'lt' => 'Lithuanian',
            'lv' => 'Latvian',
            'nb' => 'Norwegian (Bokmål)',
            'nl' => 'Dutch',
            'pl' => 'Polish',
            'pt' => 'Portuguese',
            'pt-BR' => 'Portuguese (Brazilian)',
            'pt-PT' => 'Portuguese (European)',
            'ro' => 'Romanian',
            'ru' => 'Russian',
            'sk' => 'Slovak',
            'sl' => 'Slovenian',
            'sv' => 'Swedish',
            'tr' => 'Turkish',
            'uk' => 'Ukrainian',
            'zh' => 'Chinese (simplified)',
        ),
        'translate' => 's2j_sg_deepl_translate',
    );
}

/**
 * Normalize DeepL API key paste noise.
 *
 * @param string $api_key Raw key.
 * @return string
 */
function s2j_sg_deepl_normalize_api_key($api_key) {
    $api_key = trim((string) $api_key);
    $api_key = preg_replace('/^DeepL-Auth-Key\s+/i', '', $api_key);
    return trim($api_key, " \t\"'");
}

/**
 * Resolve DeepL translate endpoint.
 *
 * Note (2026): some paid/Individual API keys still end with ":fx" but must use
 * api.deepl.com. Do not treat ":fx" as Free-only anymore.
 *
 * @param string $api_key Normalized DeepL auth key.
 * @param string $plan    auto|free|pro.
 * @return string
 */
function s2j_sg_deepl_endpoint($api_key, $plan = 'auto') {
    unset($api_key); // Endpoint no longer inferred from key suffix.
    $plan = strtolower(trim((string) $plan));

    if ($plan === 'free') {
        return 'https://api-free.deepl.com/v2/translate';
    }

    // auto and pro: prefer api.deepl.com (Free plan is explicit only).
    return 'https://api.deepl.com/v2/translate';
}

/**
 * Host label for error messages.
 *
 * @param string $endpoint Endpoint URL.
 * @return string
 */
function s2j_sg_deepl_host_label($endpoint) {
    return (strpos($endpoint, 'api-free.') !== false) ? 'api-free.deepl.com' : 'api.deepl.com';
}

/**
 * GET DeepL /v2/usage for endpoint / scope diagnostics.
 *
 * @param string $host_base https://api.deepl.com or https://api-free.deepl.com
 * @param string $api_key   Auth key.
 * @return array{status:int,message:string}
 */
function s2j_sg_deepl_probe_usage($host_base, $api_key) {
    $url = rtrim($host_base, '/') . '/v2/usage';
    if (!function_exists('curl_init')) {
        return array('status' => 0, 'message' => '');
    }

    $ch = curl_init($url);
    if ($ch === false) {
        return array('status' => 0, 'message' => '');
    }

    $version = defined('S2J_SLUG_GENERATER_VERSION') ? S2J_SLUG_GENERATER_VERSION : '1.0';
    curl_setopt_array(
        $ch,
        array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => array(
                'Authorization: DeepL-Auth-Key ' . $api_key,
                'User-Agent: S2J-Slug-Generater/' . $version,
            ),
        )
    );

    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $message = '';
    $data = is_string($raw) ? json_decode($raw, true) : null;
    if (is_array($data) && isset($data['message']) && is_string($data['message'])) {
        $message = $data['message'];
    }

    return array(
        'status' => $status,
        'message' => $message,
    );
}

/**
 * Build a precise auth/scope error after translate 403.
 *
 * @param string $api_key     Auth key.
 * @param string $endpoint    Translate endpoint that failed.
 * @param string $api_message DeepL message from translate.
 * @return string
 */
function s2j_sg_deepl_auth_failure_message($api_key, $endpoint, $api_message) {
    $pro_usage = s2j_sg_deepl_probe_usage('https://api.deepl.com', $api_key);
    $free_usage = s2j_sg_deepl_probe_usage('https://api-free.deepl.com', $api_key);

    // Key works on Pro host for /usage, but translate is forbidden → scope/plan issue.
    if ($pro_usage['status'] === 200) {
        return __(
            'DeepL accepted this API key on api.deepl.com, but Translate is forbidden. DeepL Individual (translator) is separate from DeepL API Translate. Open https://www.deepl.com/your-account/keys and create/use an API key that includes the Translate scope (or a DeepL API plan with Translate).',
            's2j-slug-generater'
        );
    }

    if (
        $free_usage['status'] === 403
        && $free_usage['message'] !== ''
        && stripos($free_usage['message'], 'api.deepl.com') !== false
    ) {
        return __(
            'DeepL reports this key must use api.deepl.com (not api-free). Set DeepL API Plan to “Pro”, then ensure the key has Translate permission.',
            's2j-slug-generater'
        );
    }

    if ($free_usage['status'] === 200) {
        return __(
            'DeepL accepted this API key on api-free.deepl.com, but Translate is forbidden. Check that the key includes the Translate scope.',
            's2j-slug-generater'
        );
    }

    $parts = array();
    $parts[] = sprintf(
        /* translators: %s: host */
        __('DeepL rejected Translate on %s.', 's2j-slug-generater'),
        s2j_sg_deepl_host_label($endpoint)
    );
    if ($api_message !== '') {
        $parts[] = $api_message;
    }

    return implode(' ', $parts);
}

/**
 * Normalize language codes for DeepL API.
 *
 * @param string $code      Language code from settings.
 * @param bool   $as_target Whether used as target_lang.
 * @return string
 */
function s2j_sg_deepl_language_code($code, $as_target = false) {
    $code = str_replace('_', '-', trim((string) $code));
    $lower = strtolower($code);

    if ($lower === 'en' || $lower === 'en-us' || $lower === 'en-gb') {
        if (!$as_target) {
            return 'EN';
        }
        if ($lower === 'en-gb') {
            return 'EN-GB';
        }
        return 'EN-US';
    }

    if ($lower === 'pt' || $lower === 'pt-pt' || $lower === 'pt-br') {
        if (!$as_target) {
            return 'PT';
        }
        if ($lower === 'pt-br') {
            return 'PT-BR';
        }
        return 'PT-PT';
    }

    if ($lower === 'zh' || $lower === 'zh-cn') {
        return 'ZH';
    }

    return strtoupper($code);
}

/**
 * POST JSON to DeepL via cURL (avoids WP HTTP Authorization-header quirks).
 *
 * @param string $endpoint URL.
 * @param string $api_key  Auth key.
 * @param array  $payload  JSON body.
 * @return array{ok:true,status:int,data:array|null,raw:string}|array{ok:false,error:array{code:string,message:string}}
 */
function s2j_sg_deepl_request($endpoint, $api_key, array $payload) {
    if (!function_exists('curl_init')) {
        return array(
            'ok' => false,
            'error' => array(
                'code' => 'http_error',
                'message' => __('Translation request failed: cURL is not available.', 's2j-slug-generater'),
            ),
        );
    }

    $body = wp_json_encode($payload);
    if ($body === false) {
        return array(
            'ok' => false,
            'error' => array(
                'code' => 'parse_error',
                'message' => __('Translation request failed: could not encode JSON payload.', 's2j-slug-generater'),
            ),
        );
    }

    $ch = curl_init($endpoint);
    if ($ch === false) {
        return array(
            'ok' => false,
            'error' => array(
                'code' => 'http_error',
                'message' => __('Translation request failed: could not initialize cURL.', 's2j-slug-generater'),
            ),
        );
    }

    $version = defined('S2J_SLUG_GENERATER_VERSION') ? S2J_SLUG_GENERATER_VERSION : '1.0';

    curl_setopt_array(
        $ch,
        array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => array(
                'Authorization: DeepL-Auth-Key ' . $api_key,
                'Content-Type: application/json',
                'User-Agent: S2J-Slug-Generater/' . $version,
            ),
        )
    );

    $raw = curl_exec($ch);
    if ($raw === false) {
        $err = curl_error($ch);
        curl_close($ch);
        return array(
            'ok' => false,
            'error' => array(
                'code' => 'http_error',
                'message' => sprintf(
                    /* translators: %s: error message */
                    __('Translation request failed: %s', 's2j-slug-generater'),
                    $err
                ),
            ),
        );
    }

    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($raw, true);

    return array(
        'ok' => true,
        'status' => $status,
        'data' => is_array($data) ? $data : null,
        'raw' => $raw,
    );
}

/**
 * Map DeepL HTTP result to domain Result.
 *
 * @param array  $result   s2j_sg_deepl_request result.
 * @param string $endpoint Endpoint used.
 * @param string $api_key  Key (for :fx hint only).
 * @return array{ok:true,value:string}|array{ok:false,error:array{code:string,message:string}}
 */
function s2j_sg_deepl_map_result(array $result, $endpoint, $api_key) {
    if (!$result['ok']) {
        return $result;
    }

    $status = (int) $result['status'];
    $data = $result['data'];
    $api_message = '';
    if (is_array($data) && isset($data['message']) && is_string($data['message'])) {
        $api_message = $data['message'];
    }

    if ($status === 403 || $status === 401) {
        return array(
            'ok' => false,
            'error' => array(
                'code' => 'invalid_api_key',
                'message' => s2j_sg_deepl_auth_failure_message($api_key, $endpoint, $api_message),
            ),
        );
    }

    if ($status < 200 || $status >= 300 || !is_array($data) || !isset($data['translations'][0]['text'])) {
        return array(
            'ok' => false,
            'error' => array(
                'code' => 'parse_error',
                'message' => sprintf(
                    /* translators: 1: HTTP status, 2: optional API message */
                    __('Translation failed: unexpected DeepL response (HTTP %1$d).%2$s', 's2j-slug-generater'),
                    $status,
                    $api_message !== '' ? ' ' . $api_message : ''
                ),
            ),
        );
    }

    return array(
        'ok' => true,
        'value' => (string) $data['translations'][0]['text'],
    );
}

/**
 * DeepL translate Adapter.
 *
 * @param array $req TranslateRequest (+ optional deeplApiPlan).
 * @return array{ok:true,value:string}|array{ok:false,error:array{code:string,message:string}}
 */
function s2j_sg_deepl_translate(array $req) {
    $api_key = s2j_sg_deepl_normalize_api_key(isset($req['apiKey']) ? $req['apiKey'] : '');
    if ($api_key === '') {
        return array(
            'ok' => false,
            'error' => array(
                'code' => 'missing_translation_api_key',
                'message' => __(
                    'Translation API key is not configured. Open Settings → S2J Slug Generater and enter your translation API key.',
                    's2j-slug-generater'
                ),
            ),
        );
    }

    $plan = isset($req['deeplApiPlan'])
        ? (string) $req['deeplApiPlan']
        : (string) get_option(S2J_Slug_Generater_Plugin_Config::OPTION_DEEPL_API_PLAN, 'auto');

    $payload = array(
        'text' => array((string) $req['text']),
        'source_lang' => s2j_sg_deepl_language_code((string) $req['source'], false),
        'target_lang' => s2j_sg_deepl_language_code((string) $req['target'], true),
    );

    $endpoint = s2j_sg_deepl_endpoint($api_key, $plan);
    $result = s2j_sg_deepl_request($endpoint, $api_key, $payload);
    $mapped = s2j_sg_deepl_map_result($result, $endpoint, $api_key);

    // Auto mode: if auth fails, retry the other host once (mis-detected plan).
    if (
        !$mapped['ok']
        && isset($mapped['error']['code'])
        && $mapped['error']['code'] === 'invalid_api_key'
        && strtolower($plan) === 'auto'
        && $result['ok']
    ) {
        $alt_endpoint = (strpos($endpoint, 'api-free.') !== false)
            ? 'https://api.deepl.com/v2/translate'
            : 'https://api-free.deepl.com/v2/translate';
        $alt_result = s2j_sg_deepl_request($alt_endpoint, $api_key, $payload);
        $alt_mapped = s2j_sg_deepl_map_result($alt_result, $alt_endpoint, $api_key);
        if ($alt_mapped['ok']) {
            return $alt_mapped;
        }
    }

    return $mapped;
}
