<?php
/**
 * Pure domain functions (WP-independent).
 *
 * @package S2J_Slug_Generater
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Validate a non-empty title.
 *
 * @param mixed $title Raw title.
 * @return array{ok:true,value:string}|array{ok:false,error:array{code:string,message:string}}
 */
function s2j_sg_validate_title($title) {
    if (!is_string($title) || trim($title) === '') {
        return array(
            'ok' => false,
            'error' => array(
                'code' => 'empty_title',
                'message' => __('Please enter a post title first.', 's2j-slug-generater'),
            ),
        );
    }

    return array(
        'ok' => true,
        'value' => $title,
    );
}

/**
 * Convert similarity ratio (0.0–1.0) to percent (0.0–100.0).
 *
 * @param float $ratio Similarity ratio.
 * @return float
 */
function s2j_sg_to_percent($ratio) {
    return (float) $ratio * 100.0;
}

/**
 * Format percent for display ("n.99").
 *
 * @param float $percent Similarity percent.
 * @return string
 */
function s2j_sg_format_percent_label($percent) {
    return number_format((float) $percent, 2, '.', '');
}

/**
 * Whether ratio meets the configured threshold.
 *
 * @param float $ratio     Similarity ratio (0.0–1.0).
 * @param float $threshold Threshold ratio (0.0–1.0).
 * @return bool
 */
function s2j_sg_passes_threshold($ratio, $threshold) {
    return (float) $ratio >= (float) $threshold;
}

/**
 * Normalize candidate text to a slug (shared rule with editors).
 *
 * @param string $text Candidate text.
 * @return string
 */
function s2j_sg_normalize_to_slug($text) {
    $slug = strtolower(trim((string) $text));
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/\s+/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim((string) $slug, '-');
}
