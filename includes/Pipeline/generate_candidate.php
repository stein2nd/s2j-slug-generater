<?php
/**
 * Candidate generation orchestrator (thin pipeline).
 *
 * @package S2J_Slug_Generater
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generate a slug candidate from a title.
 *
 * @param string $title  Post title.
 * @param array  $config PluginConfig.
 * @param array  $deps   Optional overrides: translate, compareSimilarity callables.
 * @return array{ok:true,value:array}|array{ok:false,error:array{code:string,message:string}}
 */
function s2j_sg_generate_candidate($title, array $config, array $deps = array()) {
    $translate = isset($deps['translate']) && is_callable($deps['translate'])
        ? $deps['translate']
        : function ($req) use ($config) {
            return s2j_sg_translate_with_provider($req, $config['providerId']);
        };

    $compare = isset($deps['compareSimilarity']) && is_callable($deps['compareSimilarity'])
        ? $deps['compareSimilarity']
        : function ($req) use ($config) {
            $provider_id = isset($config['similarityAiProviderId']) && $config['similarityAiProviderId'] !== ''
                ? $config['similarityAiProviderId']
                : S2J_Slug_Generater_Plugin_Config::DEFAULT_SIMILARITY_AI_PROVIDER_ID;
            return s2j_sg_compare_with_similarity_provider($req, $provider_id);
        };

    $validated = s2j_sg_validate_title($title);
    if (!$validated['ok']) {
        return $validated;
    }
    $title = $validated['value'];

    if ($config['translationApiKey'] === '') {
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

    $translated = $translate(
        array(
            'text' => $title,
            'source' => $config['sourceLanguage'],
            'target' => 'en',
            'apiKey' => $config['translationApiKey'],
            'deeplApiPlan' => isset($config['deeplApiPlan']) ? $config['deeplApiPlan'] : 'auto',
        )
    );
    if (!$translated['ok']) {
        return $translated;
    }
    $translated_text = $translated['value'];

    $reversed = $translate(
        array(
            'text' => $translated_text,
            'source' => 'en',
            'target' => $config['sourceLanguage'],
            'apiKey' => $config['translationApiKey'],
            'deeplApiPlan' => isset($config['deeplApiPlan']) ? $config['deeplApiPlan'] : 'auto',
        )
    );
    if (!$reversed['ok']) {
        return $reversed;
    }
    $reversed_text = $reversed['value'];

    $similarity = $compare(
        array(
            'baseText' => $title,
            'targetText' => $reversed_text,
            'apiKey' => $config['similarityAiApiKey'],
            'model' => $config['similarityAiModel'],
            'language' => $config['sourceLanguage'],
            'locale' => $config['locale'],
        )
    );
    if (!$similarity['ok']) {
        return $similarity;
    }
    $ratio = (float) $similarity['value'];

    return array(
        'ok' => true,
        'value' => array(
            'translatedTitle' => $translated_text,
            'similarity' => s2j_sg_to_percent($ratio),
            'candidates' => array($translated_text),
            'accepted' => s2j_sg_passes_threshold($ratio, $config['similarityThreshold']),
        ),
    );
}
