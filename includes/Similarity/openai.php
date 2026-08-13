<?php
/**
 * OpenAI similarity AI provider descriptor + Adapter.
 *
 * @package S2J_Slug_Generater
 */

if (!defined('ABSPATH')) {
    exit;
}

use S2J\Similarity\Application\SimilarityService;
use S2J\Similarity\Infrastructure\Embedding\OpenAIEmbeddingStrategy;

/**
 * OpenAI similarity AI provider descriptor.
 *
 * @return array
 */
function s2j_sg_similarity_ai_provider_openai() {
    return array(
        'id' => 'openai',
        'signupUrl' => 'https://platform.openai.com/signup',
        'billingUrl' => 'https://platform.openai.com/settings/organization/billing',
        'keysUrl' => 'https://platform.openai.com/api-keys',
        'models' => array(
            'text-embedding-3-small',
            'text-embedding-3-large',
            'text-embedding-ada-002',
        ),
        'compare' => 's2j_sg_similarity_compare_openai',
    );
}

/**
 * OpenAI embedding cosine similarity Adapter.
 *
 * Library API: SimilarityService::similarity($a, $b, $model).
 * language / locale are accepted on the request for config parity but not passed.
 *
 * @param array $req SimilarityRequest-like array.
 * @return array{ok:true,value:float}|array{ok:false,error:array{code:string,message:string}}
 */
function s2j_sg_similarity_compare_openai(array $req) {
    $api_key = isset($req['apiKey']) ? (string) $req['apiKey'] : '';
    if ($api_key === '') {
        return array(
            'ok' => false,
            'error' => array(
                'code' => 'missing_similarity_ai_key',
                'message' => __(
                    'Similarity AI API key is not configured. Open Settings → S2J Slug Generater and enter your embedding API key. This key is separate from the translation API key.',
                    's2j-slug-generater'
                ),
            ),
        );
    }

    if (!class_exists(SimilarityService::class) || !class_exists(OpenAIEmbeddingStrategy::class)) {
        return array(
            'ok' => false,
            'error' => array(
                'code' => 'similarity_unavailable',
                'message' => __('Similarity service is not available. Run Composer install.', 's2j-slug-generater'),
            ),
        );
    }

    $provider = s2j_sg_similarity_ai_provider_openai();
    $model = isset($req['model']) && $req['model'] !== ''
        ? (string) $req['model']
        : $provider['models'][0];

    try {
        $strategy = new OpenAIEmbeddingStrategy($api_key, $model);
        $service = new SimilarityService($strategy);
        $ratio = $service->similarity(
            (string) $req['baseText'],
            (string) $req['targetText'],
            $model
        );

        return array(
            'ok' => true,
            'value' => (float) $ratio,
        );
    } catch (Throwable $e) {
        return array(
            'ok' => false,
            'error' => array(
                'code' => 'similarity_error',
                'message' => sprintf(
                    /* translators: %s: error message */
                    __('Similarity comparison failed: %s', 's2j-slug-generater'),
                    $e->getMessage()
                ),
            ),
        );
    }
}
