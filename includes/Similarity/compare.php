<?php
/**
 * Similarity Adapter (s2j/similarity-service).
 *
 * @package S2J_Slug_Generater
 */

if (!defined('ABSPATH')) {
    exit;
}

use S2J\Similarity\Application\SimilarityService;
use S2J\Similarity\Infrastructure\Embedding\OpenAIEmbeddingStrategy;

/**
 * Compare similarity via embedding cosine similarity.
 *
 * Library API: SimilarityService::similarity($a, $b, $model).
 * language / locale are accepted on the request for config parity but not passed.
 *
 * @param array $req SimilarityRequest-like array.
 * @return array{ok:true,value:float}|array{ok:false,error:array{code:string,message:string}}
 */
function s2j_sg_compare_similarity(array $req) {
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

    $model = isset($req['model']) && $req['model'] !== ''
        ? (string) $req['model']
        : 'text-embedding-3-small';

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
