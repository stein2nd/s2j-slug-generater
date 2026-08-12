<?php
/**
 * Settings page Facade (Config load / edit / save only).
 *
 * @package S2J_Slug_Generater
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin settings page.
 */
class S2J_Slug_Generater_Settings_Page {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'init_settings'));
    }

    /**
     * Add options page.
     */
    public function add_admin_menu() {
        add_options_page(
            __('S2J Slug Generater Settings', 's2j-slug-generater'),
            __('S2J Slug Generater', 's2j-slug-generater'),
            'manage_options',
            's2j-slug-generater',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register settings and fields.
     */
    public function init_settings() {
        $option_group = 's2j_slug_generater_options';

        register_setting($option_group, S2J_Slug_Generater_Plugin_Config::OPTION_PROVIDER_ID, array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => S2J_Slug_Generater_Plugin_Config::DEFAULT_PROVIDER_ID,
        ));
        register_setting($option_group, S2J_Slug_Generater_Plugin_Config::OPTION_TRANSLATION_API_KEY, array(
            'type' => 'string',
            'sanitize_callback' => array($this, 'sanitize_api_key'),
            'default' => '',
        ));
        register_setting($option_group, S2J_Slug_Generater_Plugin_Config::OPTION_DEEPL_API_PLAN, array(
            'type' => 'string',
            'sanitize_callback' => array($this, 'sanitize_deepl_api_plan'),
            'default' => S2J_Slug_Generater_Plugin_Config::DEFAULT_DEEPL_API_PLAN,
        ));
        register_setting($option_group, S2J_Slug_Generater_Plugin_Config::OPTION_SOURCE_LANGUAGE, array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => S2J_Slug_Generater_Plugin_Config::DEFAULT_SOURCE_LANGUAGE,
        ));
        register_setting($option_group, S2J_Slug_Generater_Plugin_Config::OPTION_SIMILARITY_AI_API_KEY, array(
            'type' => 'string',
            'sanitize_callback' => array($this, 'sanitize_api_key'),
            'default' => '',
        ));
        register_setting($option_group, S2J_Slug_Generater_Plugin_Config::OPTION_SIMILARITY_AI_MODEL, array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => S2J_Slug_Generater_Plugin_Config::DEFAULT_SIMILARITY_AI_MODEL,
        ));
        register_setting($option_group, S2J_Slug_Generater_Plugin_Config::OPTION_LOCALE, array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => S2J_Slug_Generater_Plugin_Config::DEFAULT_LOCALE,
        ));
        register_setting($option_group, S2J_Slug_Generater_Plugin_Config::OPTION_SIMILARITY_THRESHOLD, array(
            'type' => 'number',
            'sanitize_callback' => array($this, 'sanitize_threshold'),
            'default' => S2J_Slug_Generater_Plugin_Config::DEFAULT_SIMILARITY_THRESHOLD,
        ));

        add_settings_section(
            's2j_slug_generater_translation_section',
            __('Translation Service Settings', 's2j-slug-generater'),
            array($this, 'render_translation_section'),
            's2j-slug-generater'
        );

        add_settings_field(
            S2J_Slug_Generater_Plugin_Config::OPTION_PROVIDER_ID,
            __('Translation Service', 's2j-slug-generater'),
            array($this, 'render_provider_field'),
            's2j-slug-generater',
            's2j_slug_generater_translation_section'
        );
        add_settings_field(
            S2J_Slug_Generater_Plugin_Config::OPTION_TRANSLATION_API_KEY,
            __('Translation API Key', 's2j-slug-generater'),
            array($this, 'render_translation_api_key_field'),
            's2j-slug-generater',
            's2j_slug_generater_translation_section'
        );
        add_settings_field(
            S2J_Slug_Generater_Plugin_Config::OPTION_DEEPL_API_PLAN,
            __('DeepL API Plan', 's2j-slug-generater'),
            array($this, 'render_deepl_api_plan_field'),
            's2j-slug-generater',
            's2j_slug_generater_translation_section'
        );
        add_settings_field(
            S2J_Slug_Generater_Plugin_Config::OPTION_SOURCE_LANGUAGE,
            __('Source Language', 's2j-slug-generater'),
            array($this, 'render_source_language_field'),
            's2j-slug-generater',
            's2j_slug_generater_translation_section'
        );

        add_settings_section(
            's2j_slug_generater_similarity_section',
            __('Similarity Settings', 's2j-slug-generater'),
            array($this, 'render_similarity_section'),
            's2j-slug-generater'
        );

        add_settings_field(
            S2J_Slug_Generater_Plugin_Config::OPTION_SIMILARITY_AI_API_KEY,
            __('Similarity AI API Key', 's2j-slug-generater'),
            array($this, 'render_similarity_ai_key_field'),
            's2j-slug-generater',
            's2j_slug_generater_similarity_section'
        );
        add_settings_field(
            S2J_Slug_Generater_Plugin_Config::OPTION_SIMILARITY_AI_MODEL,
            __('Similarity AI Model', 's2j-slug-generater'),
            array($this, 'render_similarity_ai_model_field'),
            's2j-slug-generater',
            's2j_slug_generater_similarity_section'
        );
        add_settings_field(
            S2J_Slug_Generater_Plugin_Config::OPTION_LOCALE,
            __('Locale', 's2j-slug-generater'),
            array($this, 'render_locale_field'),
            's2j-slug-generater',
            's2j_slug_generater_similarity_section'
        );
        add_settings_field(
            S2J_Slug_Generater_Plugin_Config::OPTION_SIMILARITY_THRESHOLD,
            __('Similarity Threshold', 's2j-slug-generater'),
            array($this, 'render_threshold_field'),
            's2j-slug-generater',
            's2j_slug_generater_similarity_section'
        );
    }

    /**
     * Sanitize API keys without aggressive text filtering.
     *
     * @param mixed $value Raw value.
     * @return string
     */
    public function sanitize_api_key($value) {
        $value = trim((string) $value);
        $value = str_replace("\0", '', $value);
        $value = preg_replace('/^DeepL-Auth-Key\s+/i', '', $value);
        return trim($value, " \t\"'");
    }

    /**
     * Sanitize DeepL API plan.
     *
     * @param mixed $value Raw value.
     * @return string
     */
    public function sanitize_deepl_api_plan($value) {
        $value = sanitize_text_field((string) $value);
        if (!in_array($value, array('auto', 'free', 'pro'), true)) {
            return S2J_Slug_Generater_Plugin_Config::DEFAULT_DEEPL_API_PLAN;
        }
        return $value;
    }

    /**
     * Sanitize threshold as ratio 0.0–1.0 step 0.1.
     *
     * @param mixed $value Raw value.
     * @return float
     */
    public function sanitize_threshold($value) {
        return S2J_Slug_Generater_Plugin_Config::clamp_threshold((float) $value);
    }

    /**
     * Translation section description.
     */
    public function render_translation_section() {
        echo '<p>' . esc_html__('Configure the translation service used for slug candidate generation.', 's2j-slug-generater') . '</p>';
    }

    /**
     * Similarity section description.
     */
    public function render_similarity_section() {
        echo '<p>' . esc_html__('Configure embedding-based similarity checks. The similarity AI API key is separate from the translation API key.', 's2j-slug-generater') . '</p>';
    }

    /**
     * Provider select + help links.
     */
    public function render_provider_field() {
        $config = S2J_Slug_Generater_Plugin_Config::load();
        $providers = s2j_sg_providers();
        $current = $config['providerId'];

        echo '<select name="' . esc_attr(S2J_Slug_Generater_Plugin_Config::OPTION_PROVIDER_ID) . '" id="s2j_slug_generater_translation_service">';
        foreach ($providers as $id => $provider) {
            $label = $id === 'deepl' ? __('DeepL API', 's2j-slug-generater') : __('Google Translate API', 's2j-slug-generater');
            echo '<option value="' . esc_attr($id) . '" ' . selected($current, $id, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';

        $lookup = s2j_sg_lookup_provider($current);
        if ($lookup['ok']) {
            echo '<p class="description" id="s2j-provider-help">' . s2j_sg_format_api_key_help($lookup['value']) . '</p>';
        }
    }

    /**
     * Translation API key field.
     */
    public function render_translation_api_key_field() {
        $config = S2J_Slug_Generater_Plugin_Config::load();
        echo '<input type="text" name="' . esc_attr(S2J_Slug_Generater_Plugin_Config::OPTION_TRANSLATION_API_KEY) . '" id="s2j_slug_generater_api_key" value="' . esc_attr($config['translationApiKey']) . '" class="regular-text" autocomplete="off" spellcheck="false" />';
        echo '<p class="description">' . esc_html__('Paste the key from DeepL → Account → API Keys. DeepL Individual (translator app) is not the same product as DeepL API Translate.', 's2j-slug-generater') . '</p>';
    }

    /**
     * DeepL API plan select (free/pro/auto).
     */
    public function render_deepl_api_plan_field() {
        $config = S2J_Slug_Generater_Plugin_Config::load();
        $current = $config['deeplApiPlan'];
        $options = array(
            'auto' => __('Auto (prefer api.deepl.com, retry api-free on failure)', 's2j-slug-generater'),
            'pro' => __('DeepL API Pro host (api.deepl.com)', 's2j-slug-generater'),
            'free' => __('DeepL API Free host (api-free.deepl.com)', 's2j-slug-generater'),
        );

        echo '<select name="' . esc_attr(S2J_Slug_Generater_Plugin_Config::OPTION_DEEPL_API_PLAN) . '" id="s2j_slug_generater_deepl_api_plan">';
        foreach ($options as $value => $label) {
            echo '<option value="' . esc_attr($value) . '"' . selected($current, $value, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . esc_html__('Your key must allow the Translate API. A DeepL Individual translator subscription alone is not enough if the key lacks Translate scope.', 's2j-slug-generater') . '</p>';
    }

    /**
     * Source language select (provider-driven).
     */
    public function render_source_language_field() {
        $config = S2J_Slug_Generater_Plugin_Config::load();
        $providers = s2j_sg_providers();
        $current_provider = $config['providerId'];
        $current_lang = $config['sourceLanguage'];
        $languages = isset($providers[$current_provider]['languages'])
            ? $providers[$current_provider]['languages']
            : $providers['deepl']['languages'];

        $lang_maps = array();
        foreach ($providers as $id => $provider) {
            $lang_maps[$id] = $provider['languages'];
        }

        echo '<script type="text/javascript">var s2jProviderLanguages = ' . wp_json_encode($lang_maps) . ';</script>';

        echo '<select name="' . esc_attr(S2J_Slug_Generater_Plugin_Config::OPTION_SOURCE_LANGUAGE) . '" id="s2j_slug_generater_source_language">';
        foreach ($languages as $code => $name) {
            echo '<option value="' . esc_attr($code) . '"' . selected($current_lang, $code, false) . '>' . esc_html($name) . '</option>';
        }
        echo '</select>';
    }

    /**
     * Similarity AI API key field.
     */
    public function render_similarity_ai_key_field() {
        $config = S2J_Slug_Generater_Plugin_Config::load();
        echo '<input type="text" name="' . esc_attr(S2J_Slug_Generater_Plugin_Config::OPTION_SIMILARITY_AI_API_KEY) . '" id="s2j_slug_generater_similarity_ai_api_key" value="' . esc_attr($config['similarityAiApiKey']) . '" class="regular-text" autocomplete="off" spellcheck="false" />';
        echo '<p class="description">';
        echo sprintf(
            /* translators: %s: OpenAI API keys URL */
            __('Get an embedding API key from <a target="_blank" href="%s">OpenAI API keys</a>. This is separate from the translation API key.', 's2j-slug-generater'),
            esc_url('https://platform.openai.com/api-keys')
        );
        echo '</p>';
    }

    /**
     * Similarity AI model select.
     */
    public function render_similarity_ai_model_field() {
        $config = S2J_Slug_Generater_Plugin_Config::load();
        $models = array(
            'text-embedding-3-small' => 'text-embedding-3-small',
            'text-embedding-3-large' => 'text-embedding-3-large',
            'text-embedding-ada-002' => 'text-embedding-ada-002',
        );

        echo '<select name="' . esc_attr(S2J_Slug_Generater_Plugin_Config::OPTION_SIMILARITY_AI_MODEL) . '" id="s2j_slug_generater_similarity_ai_model">';
        foreach ($models as $value => $label) {
            echo '<option value="' . esc_attr($value) . '"' . selected($config['similarityAiModel'], $value, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
    }

    /**
     * Locale select.
     */
    public function render_locale_field() {
        $config = S2J_Slug_Generater_Plugin_Config::load();
        $locales = array(
            'ja_JP' => 'ja_JP',
            'en_US' => 'en_US',
            'fr_FR' => 'fr_FR',
        );

        echo '<select name="' . esc_attr(S2J_Slug_Generater_Plugin_Config::OPTION_LOCALE) . '" id="s2j_slug_generater_locale">';
        foreach ($locales as $value => $label) {
            echo '<option value="' . esc_attr($value) . '"' . selected($config['locale'], $value, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . esc_html__('Stored for future use. Similarity currently uses the library API (texts + model).', 's2j-slug-generater') . '</p>';
    }

    /**
     * Threshold slider (ratio 0.0–1.0, display as %).
     */
    public function render_threshold_field() {
        $config = S2J_Slug_Generater_Plugin_Config::load();
        $threshold = $config['similarityThreshold'];
        $percent = s2j_sg_format_percent_label(s2j_sg_to_percent($threshold));

        echo '<input type="range" name="' . esc_attr(S2J_Slug_Generater_Plugin_Config::OPTION_SIMILARITY_THRESHOLD) . '" id="s2j_slug_generater_similarity_threshold" min="0" max="1" step="0.1" value="' . esc_attr((string) $threshold) . '" />';
        echo ' <span id="s2j_slug_generater_threshold_value">' . esc_html($percent) . '%</span>';
        echo '<p class="description">' . esc_html__('Minimum cosine similarity required to accept a candidate (0–100%).', 's2j-slug-generater') . '</p>';
    }

    /**
     * Render settings page + provider/threshold scripts.
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $providers = s2j_sg_providers();
        $help_map = array();
        foreach ($providers as $id => $provider) {
            $help_map[$id] = s2j_sg_format_api_key_help($provider);
        }

        echo '<div class="wrap">';
        echo '<h1>' . esc_html(get_admin_page_title()) . '</h1>';
        echo '<form action="options.php" method="post">';
        settings_fields('s2j_slug_generater_options');
        do_settings_sections('s2j-slug-generater');
        submit_button();
        echo '</form>';
        echo '</div>';

        $help_json = wp_json_encode($help_map);
        echo '<script>
        jQuery(function($) {
            var helpMap = ' . $help_json . ';
            var $service = $("#s2j_slug_generater_translation_service");
            var $lang = $("#s2j_slug_generater_source_language");
            var $threshold = $("#s2j_slug_generater_similarity_threshold");
            var $thresholdLabel = $("#s2j_slug_generater_threshold_value");

            function updateLanguages() {
                var id = $service.val();
                var languages = (window.s2jProviderLanguages && window.s2jProviderLanguages[id]) || {};
                var current = $lang.val();
                $lang.empty();
                $.each(languages, function(code, name) {
                    $lang.append($("<option/>").attr("value", code).text(name));
                });
                if (languages[current]) {
                    $lang.val(current);
                } else {
                    var keys = Object.keys(languages);
                    if (keys.indexOf("ja") !== -1) {
                        $lang.val("ja");
                    } else if (keys.length) {
                        $lang.val(keys[0]);
                    }
                }
            }

            function updateHelp() {
                var id = $service.val();
                if (helpMap[id]) {
                    $("#s2j-provider-help").html(helpMap[id]);
                }
            }

            function updateThresholdLabel() {
                var ratio = parseFloat($threshold.val(), 10) || 0;
                $thresholdLabel.text((ratio * 100).toFixed(2) + "%");
            }

            $service.on("change", function() {
                updateLanguages();
                updateHelp();
            });
            $threshold.on("input change", updateThresholdLabel);
            updateThresholdLabel();
        });
        </script>';
    }
}
