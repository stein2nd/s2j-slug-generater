<?php
/**
 * PluginConfig Option Adapter (load / save / migrate).
 *
 * @package S2J_Slug_Generater
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Option key constants and Config Adapter.
 */
class S2J_Slug_Generater_Plugin_Config {
    const OPTION_PROVIDER_ID = 's2j_slug_generater_translation_service';
    const OPTION_TRANSLATION_API_KEY = 's2j_slug_generater_api_key';
    const OPTION_SOURCE_LANGUAGE = 's2j_slug_generater_source_language';
    const OPTION_SIMILARITY_THRESHOLD = 's2j_slug_generater_similarity_threshold';
    const OPTION_SIMILARITY_AI_API_KEY = 's2j_slug_generater_similarity_ai_api_key';
    const OPTION_SIMILARITY_AI_MODEL = 's2j_slug_generater_similarity_ai_model';
    const OPTION_LOCALE = 's2j_slug_generater_locale';
    const OPTION_DEEPL_API_PLAN = 's2j_slug_generater_deepl_api_plan';
    const OPTION_THRESHOLD_MIGRATED = 's2j_slug_generater_threshold_migrated_v1';

    const DEFAULT_PROVIDER_ID = 'deepl';
    const DEFAULT_SOURCE_LANGUAGE = 'ja';
    const DEFAULT_SIMILARITY_THRESHOLD = 0.8;
    const DEFAULT_SIMILARITY_AI_MODEL = 'text-embedding-3-small';
    const DEFAULT_LOCALE = 'ja_JP';
    const DEFAULT_DEEPL_API_PLAN = 'auto';

    /**
     * Migrate legacy percent threshold (0–100) to ratio (0.0–1.0) once.
     */
    public static function maybe_migrate_threshold() {
        if (get_option(self::OPTION_THRESHOLD_MIGRATED, false)) {
            return;
        }

        $raw = get_option(self::OPTION_SIMILARITY_THRESHOLD, null);

        if ($raw === null || $raw === false) {
            // Not registered: leave unset so loadConfig uses float default.
            update_option(self::OPTION_THRESHOLD_MIGRATED, 1);
            return;
        }

        $value = (float) $raw;

        // Legacy percent scale (e.g. 80) → ratio (0.8).
        if ($value > 1.0) {
            $value = $value / 100.0;
        }

        $value = self::clamp_threshold($value);
        update_option(self::OPTION_SIMILARITY_THRESHOLD, $value);
        update_option(self::OPTION_THRESHOLD_MIGRATED, 1);
    }

    /**
     * Load immutable PluginConfig from Options.
     *
     * @return array{
     *   providerId: string,
     *   translationApiKey: string,
     *   sourceLanguage: string,
     *   similarityAiApiKey: string,
     *   similarityAiModel: string,
     *   locale: string,
     *   similarityThreshold: float,
     *   deeplApiPlan: string
     * }
     */
    public static function load() {
        self::maybe_migrate_threshold();

        $threshold_raw = get_option(self::OPTION_SIMILARITY_THRESHOLD, null);
        if ($threshold_raw === null || $threshold_raw === false) {
            $threshold = self::DEFAULT_SIMILARITY_THRESHOLD;
        } else {
            $threshold = self::clamp_threshold((float) $threshold_raw);
        }

        $plan = (string) get_option(self::OPTION_DEEPL_API_PLAN, self::DEFAULT_DEEPL_API_PLAN);
        if (!in_array($plan, array('auto', 'free', 'pro'), true)) {
            $plan = self::DEFAULT_DEEPL_API_PLAN;
        }

        return array(
            'providerId' => (string) get_option(self::OPTION_PROVIDER_ID, self::DEFAULT_PROVIDER_ID),
            'translationApiKey' => trim((string) get_option(self::OPTION_TRANSLATION_API_KEY, '')),
            'sourceLanguage' => (string) get_option(self::OPTION_SOURCE_LANGUAGE, self::DEFAULT_SOURCE_LANGUAGE),
            'similarityAiApiKey' => trim((string) get_option(self::OPTION_SIMILARITY_AI_API_KEY, '')),
            'similarityAiModel' => (string) get_option(self::OPTION_SIMILARITY_AI_MODEL, self::DEFAULT_SIMILARITY_AI_MODEL),
            'locale' => (string) get_option(self::OPTION_LOCALE, self::DEFAULT_LOCALE),
            'similarityThreshold' => $threshold,
            'deeplApiPlan' => $plan,
        );
    }

    /**
     * Persist PluginConfig fields to Options.
     *
     * @param array $config PluginConfig-like array.
     */
    public static function save(array $config) {
        if (isset($config['providerId'])) {
            update_option(self::OPTION_PROVIDER_ID, sanitize_text_field($config['providerId']));
        }
        if (isset($config['translationApiKey'])) {
            update_option(self::OPTION_TRANSLATION_API_KEY, sanitize_text_field($config['translationApiKey']));
        }
        if (isset($config['sourceLanguage'])) {
            update_option(self::OPTION_SOURCE_LANGUAGE, sanitize_text_field($config['sourceLanguage']));
        }
        if (isset($config['similarityAiApiKey'])) {
            update_option(self::OPTION_SIMILARITY_AI_API_KEY, sanitize_text_field($config['similarityAiApiKey']));
        }
        if (isset($config['similarityAiModel'])) {
            update_option(self::OPTION_SIMILARITY_AI_MODEL, sanitize_text_field($config['similarityAiModel']));
        }
        if (isset($config['locale'])) {
            update_option(self::OPTION_LOCALE, sanitize_text_field($config['locale']));
        }
        if (isset($config['similarityThreshold'])) {
            update_option(
                self::OPTION_SIMILARITY_THRESHOLD,
                self::clamp_threshold((float) $config['similarityThreshold'])
            );
        }
        if (isset($config['deeplApiPlan'])) {
            $plan = sanitize_text_field($config['deeplApiPlan']);
            if (!in_array($plan, array('auto', 'free', 'pro'), true)) {
                $plan = self::DEFAULT_DEEPL_API_PLAN;
            }
            update_option(self::OPTION_DEEPL_API_PLAN, $plan);
        }
    }

    /**
     * All option keys owned by this plugin (for uninstall).
     *
     * @return string[]
     */
    public static function option_keys() {
        return array(
            self::OPTION_PROVIDER_ID,
            self::OPTION_TRANSLATION_API_KEY,
            self::OPTION_SOURCE_LANGUAGE,
            self::OPTION_SIMILARITY_THRESHOLD,
            self::OPTION_SIMILARITY_AI_API_KEY,
            self::OPTION_SIMILARITY_AI_MODEL,
            self::OPTION_LOCALE,
            self::OPTION_DEEPL_API_PLAN,
            self::OPTION_THRESHOLD_MIGRATED,
        );
    }

    /**
     * @param float $value Raw threshold.
     * @return float Clamped to [0.0, 1.0].
     */
    public static function clamp_threshold($value) {
        if ($value < 0.0) {
            return 0.0;
        }
        if ($value > 1.0) {
            return 1.0;
        }
        return round($value, 1);
    }
}
