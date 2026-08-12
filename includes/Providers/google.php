<?php
/**
 * Google Translate provider descriptor + Adapter.
 *
 * @package S2J_Slug_Generater
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Google Translate provider descriptor.
 *
 * @return array
 */
function s2j_sg_provider_google() {
    return array(
        'id' => 'google',
        'planUrl' => 'https://cloud.google.com/translate/pricing?hl=ja',
        'freeKeyUrl' => 'https://cloud.google.com/translate/docs/setup?hl=ja',
        'endpoint' => 'https://translation.googleapis.com/language/translate/v2',
        'languages' => array(
            'ar' => 'Arabic',
            'zh' => 'Chinese (Simplified)',
            'zh-TW' => 'Chinese (Traditional)',
            'cs' => 'Czech',
            'da' => 'Danish',
            'nl' => 'Dutch',
            'en' => 'English',
            'fi' => 'Finnish',
            'fr' => 'French',
            'de' => 'German',
            'el' => 'Greek',
            'hi' => 'Hindi',
            'hu' => 'Hungarian',
            'id' => 'Indonesian',
            'it' => 'Italian',
            'ja' => 'Japanese',
            'ko' => 'Korean',
            'no' => 'Norwegian',
            'pl' => 'Polish',
            'pt' => 'Portuguese',
            'pt-BR' => 'Portuguese (Brazil)',
            'ro' => 'Romanian',
            'ru' => 'Russian',
            'es' => 'Spanish',
            'sv' => 'Swedish',
            'th' => 'Thai',
            'tr' => 'Turkish',
            'uk' => 'Ukrainian',
            'vi' => 'Vietnamese',
        ),
        'translate' => 's2j_sg_google_translate',
    );
}

/**
 * Google Translate Adapter.
 *
 * @param array $req TranslateRequest.
 * @return array{ok:true,value:string}|array{ok:false,error:array{code:string,message:string}}
 */
function s2j_sg_google_translate(array $req) {
    $api_key = isset($req['apiKey']) ? (string) $req['apiKey'] : '';
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

    $provider = s2j_sg_provider_google();
    $response = wp_remote_post(
        $provider['endpoint'],
        array(
            'timeout' => 30,
            'body' => array(
                'key' => $api_key,
                'q' => (string) $req['text'],
                'source' => (string) $req['source'],
                'target' => (string) $req['target'],
            ),
        )
    );

    if (is_wp_error($response)) {
        return array(
            'ok' => false,
            'error' => array(
                'code' => 'http_error',
                'message' => sprintf(
                    /* translators: %s: error message */
                    __('Translation request failed: %s', 's2j-slug-generater'),
                    $response->get_error_message()
                ),
            ),
        );
    }

    $status = (int) wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if ($status === 403 || $status === 401) {
        return array(
            'ok' => false,
            'error' => array(
                'code' => 'invalid_api_key',
                'message' => __('Translation failed: invalid API key.', 's2j-slug-generater'),
            ),
        );
    }

    if (!is_array($data) || !isset($data['data']['translations'][0]['translatedText'])) {
        return array(
            'ok' => false,
            'error' => array(
                'code' => 'parse_error',
                'message' => __('Translation failed: Invalid response from Google Translate API.', 's2j-slug-generater'),
            ),
        );
    }

    return array(
        'ok' => true,
        'value' => (string) $data['data']['translations'][0]['translatedText'],
    );
}
