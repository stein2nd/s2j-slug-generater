<?php
/**
 * Settings page for S2J Slug Generater plugin
 *
 * @package S2J_Slug_Generater
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings page class
 */
class S2J_Slug_Generater_Settings_Page {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'init_settings'));
    }
    
    /**
     * Add admin menu
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
     * Initialize settings
     */
    public function init_settings() {
        register_setting('s2j_slug_generater_options', 's2j_slug_generater_translation_service');
        register_setting('s2j_slug_generater_options', 's2j_slug_generater_api_key');
        register_setting('s2j_slug_generater_options', 's2j_slug_generater_source_language');
        register_setting('s2j_slug_generater_options', 's2j_slug_generater_similarity_threshold');
        
        add_settings_section(
            's2j_slug_generater_main_section',
            __('Translation Service Settings', 's2j-slug-generater'),
            array($this, 'render_section_description'),
            's2j-slug-generater'
        );
        
        add_settings_field(
            's2j_slug_generater_translation_service',
            __('Translation Service', 's2j-slug-generater'),
            array($this, 'render_translation_service_field'),
            's2j-slug-generater',
            's2j_slug_generater_main_section'
        );
        
        add_settings_field(
            's2j_slug_generater_api_key',
            __('API Key', 's2j-slug-generater'),
            array($this, 'render_api_key_field'),
            's2j-slug-generater',
            's2j_slug_generater_main_section'
        );
        
        add_settings_field(
            's2j_slug_generater_source_language',
            __('Source Language', 's2j-slug-generater'),
            array($this, 'render_source_language_field'),
            's2j-slug-generater',
            's2j_slug_generater_main_section'
        );
        
        add_settings_field(
            's2j_slug_generater_similarity_threshold',
            __('Similarity Threshold', 's2j-slug-generater'),
            array($this, 'render_similarity_threshold_field'),
            's2j-slug-generater',
            's2j_slug_generater_main_section'
        );
    }
    
    /**
     * Render section description
     */
    public function render_section_description() {
        echo '<p>' . __('Configure the translation service API settings for slug generation.', 's2j-slug-generater') . '</p>';
    }
    
    /**
     * Render translation service field
     */
    public function render_translation_service_field() {
        $current_service = get_option('s2j_slug_generater_translation_service', 'deepl');
        
        echo '<select name="s2j_slug_generater_translation_service" id="s2j_slug_generater_translation_service">';
        echo '<option value="deepl" ' . selected($current_service, 'deepl', false) . '>' . __('DeepL API', 's2j-slug-generater') . '</option>';
        echo '<option value="google" ' . selected($current_service, 'google', false) . '>' . __('Google Translate API', 's2j-slug-generater') . '</option>';
        echo '</select>';
        
        echo '<p class="description">';
        echo sprintf(
            __('Go to <a target="_blank" href="%1$s">the API plan selection page</a> and <a target="_blank" href="%2$s">obtain a free API key</a>.', 's2j-slug-generater'),
            $this->get_plan_selection_url($current_service),
            $this->get_api_key_url($current_service)
        );
        echo '</p>';
    }
    
    /**
     * Render API key field
     */
    public function render_api_key_field() {
        $api_key = get_option('s2j_slug_generater_api_key', '');
        echo '<input type="text" name="s2j_slug_generater_api_key" id="s2j_slug_generater_api_key" value="' . esc_attr($api_key) . '" class="regular-text" />';
    }
    
    /**
     * Render source language field
     */
    public function render_source_language_field() {
        $current_language = get_option('s2j_slug_generater_source_language', 'ja');
        
        // DeepL対応言語のリスト
        $deepl_languages = array(
            'ar' => 'アラビア語',
            'it' => 'イタリア語',
            'id' => 'インドネシア語',
            'uk' => 'ウクライナ語',
            'et' => 'エストニア語',
            'nl' => 'オランダ語',
            'el' => 'ギリシャ語',
            'sv' => 'スウェーデン語',
            'es' => 'スペイン語',
            'sk' => 'スロバキア語',
            'sl' => 'スロベニア語',
            'cs' => 'チェコ語',
            'da' => 'デンマーク語',
            'de' => 'ドイツ語',
            'tr' => 'トルコ語',
            'nb' => 'ノルウェー語(ブークモール)',
            'hu' => 'ハンガリー語',
            'fi' => 'フィンランド語',
            'fr' => 'フランス語',
            'bg' => 'ブルガリア語',
            'pl' => 'ポーランド語',
            'pt' => 'ポルトガル語',
            'pt-BR' => 'ポルトガル語(ブラジル)',
            'lv' => 'ラトビア語',
            'lt' => 'リトアニア語',
            'ro' => 'ルーマニア語',
            'ru' => 'ロシア語',
            'en-US' => '英語(アメリカ)',
            'en-GB' => '英語(イギリス)',
            'ko' => '韓国語',
            'zh' => '中国語(簡体字)',
            'zh-TW' => '中国語(繁体字)',
            'ja' => '日本語'
        );
        
        // Google翻訳対応言語のリスト（主要な言語のみ）
        $google_languages = array(
            'ab' => 'アブハズ語',
            'ace' => 'アチェ語',
            'af' => 'アフリカーンス語',
            'ak' => 'トウィ語（アカン語）',
            'am' => 'アムハラ語',
            'ar' => 'アラビア語',
            'as' => 'アッサム語',
            'ay' => 'アイマラ語',
            'az' => 'アゼルバイジャン語',
            'ba' => 'バシキール語',
            'be' => 'ベラルーシ語',
            'bg' => 'ブルガリア語',
            'bn' => 'ベンガル語',
            'bs' => 'ボスニア語',
            'ca' => 'カタロニア語',
            'ceb' => 'セブアノ語',
            'co' => 'コルシカ語',
            'cs' => 'チェコ語',
            'cy' => 'ウェールズ語',
            'da' => 'デンマーク語',
            'de' => 'ドイツ語',
            'dv' => 'ディベヒ語',
            'dz' => 'ゾンカ語',
            'ee' => 'エウェ語',
            'el' => 'ギリシャ語',
            'eo' => 'エスペラント語',
            'es' => 'スペイン語',
            'et' => 'エストニア語',
            'eu' => 'バスク語',
            'fa' => 'ペルシャ語',
            'fi' => 'フィンランド語',
            'fj' => 'フィジー語',
            'fr' => 'フランス語',
            'fr-CA' => 'フランス語（カナダ）',
            'fy' => 'フリジア語',
            'ga' => 'アイルランド語',
            'gd' => 'スコットランド・ゲール語',
            'gl' => 'ガリシア語',
            'gn' => 'グアラニ語',
            'gu' => 'グジャラート語',
            'ha' => 'ハウサ語',
            'haw' => 'ハワイ語',
            'he' => 'ヘブライ語',
            'hi' => 'ヒンディー語',
            'hr' => 'クロアチア語',
            'ht' => 'クレオール語（ハイチ）',
            'hu' => 'ハンガリー語',
            'hy' => 'アルメニア語',
            'id' => 'インドネシア語',
            'ig' => 'イボ語',
            'is' => 'アイスランド語',
            'it' => 'イタリア語',
            'jv' => 'ジャワ語',
            'ka' => 'ジョージア語',
            'kk' => 'カザフ語',
            'km' => 'クメール語',
            'kn' => 'カンナダ語',
            'ko' => '韓国語',
            'ku' => 'クルド語（クルマンジー語）',
            'ky' => 'キルギス語',
            'la' => 'ラテン語',
            'lb' => 'ルクセンブルク語',
            'ln' => 'リンガラ語',
            'lo' => 'ラオ語',
            'lt' => 'リトアニア語',
            'lv' => 'ラトビア語',
            'mg' => 'マラガシ語',
            'mi' => 'マオリ語',
            'mk' => 'マケドニア語',
            'ml' => 'マラヤーラム語',
            'mn' => 'モンゴル語',
            'mr' => 'マラーティー語',
            'ms' => 'マレー語',
            'mt' => 'マルタ語',
            'my' => 'ミャンマー語（ビルマ語）',
            'ne' => 'ネパール語',
            'nl' => 'オランダ語',
            'no' => 'ノルウェー語',
            'ny' => 'チェワ語（ニャンジャ語）',
            'oc' => 'オック語',
            'om' => 'オロモ語',
            'or' => 'オリヤ語',
            'pa' => 'パンジャブ語',
            'pl' => 'ポーランド語',
            'ps' => 'パシュト語',
            'pt' => 'ポルトガル語',
            'pt-BR' => 'ポルトガル語（ブラジル）',
            'qu' => 'ケチュア語',
            'ro' => 'ルーマニア語',
            'ru' => 'ロシア語',
            'sa' => 'サンスクリット語',
            'sd' => 'シンド語',
            'si' => 'シンハラ語',
            'sk' => 'スロバキア語',
            'sl' => 'スロベニア語',
            'sm' => 'サモア語',
            'sn' => 'ショナ語',
            'so' => 'ソマリ語',
            'sq' => 'アルバニア語',
            'sr' => 'セルビア語',
            'st' => 'ソト語',
            'su' => 'スンダ語',
            'sv' => 'スウェーデン語',
            'sw' => 'スワヒリ語',
            'ta' => 'タミル語',
            'te' => 'テルグ語',
            'tg' => 'タジク語',
            'th' => 'タイ語',
            'ti' => 'ティグリニャ語',
            'tk' => 'トルクメン語',
            'tl' => 'タガログ語',
            'tr' => 'トルコ語',
            'tt' => 'タタール語',
            'ug' => 'ウイグル語',
            'uk' => 'ウクライナ語',
            'ur' => 'ウルドゥー語',
            'uz' => 'ウズベク語',
            'vi' => 'ベトナム語',
            'xh' => 'コーサ語',
            'yi' => 'イディッシュ語',
            'yo' => 'ヨルバ語',
            'yue' => '広東語',
            'zh' => '中国語（簡体字）',
            'zh-TW' => '中国語（繁体字）',
            'zu' => 'ズールー語',
            'en' => '英語',
            'ja' => '日本語'
        );
        
        // 言語データをJavaScriptに渡す
        echo '<script type="text/javascript">';
        echo 'var deeplLanguages = ' . json_encode($deepl_languages) . ';';
        echo 'var googleLanguages = ' . json_encode($google_languages) . ';';
        echo '</script>';
        
        echo '<select name="s2j_slug_generater_source_language" id="s2j_slug_generater_source_language">';
        // 初期表示はDeepL言語（デフォルト）
        foreach ($deepl_languages as $code => $name) {
            $selected = selected($current_language, $code, false);
            echo '<option value="' . esc_attr($code) . '"' . $selected . '>' . esc_html($name) . '</option>';
        }
        echo '</select>';
        
        // JavaScriptで言語切り替え機能を実装
        echo '<script type="text/javascript">
        jQuery(document).ready(function($) {
            var translationService = $("#s2j_slug_generater_translation_service");
            var sourceLanguage = $("#s2j_slug_generater_source_language");
            
            function updateSourceLanguages() {
                var selectedService = translationService.val();
                var currentValue = sourceLanguage.val();
                var languages = (selectedService === "google") ? googleLanguages : deeplLanguages;
                
                // 選択肢をクリア
                sourceLanguage.empty();
                
                // 新しい選択肢を追加
                $.each(languages, function(code, name) {
                    var selected = (code === currentValue) ? " selected" : "";
                    sourceLanguage.append(\'<option value="\' + code + \'"\' + selected + \'>\' + name + \'</option>\');
                });
                
                // 現在の値が新しい言語リストにない場合は、最初の言語を選択
                if (!languages[currentValue]) {
                    sourceLanguage.val(Object.keys(languages)[0]);
                }
            }
            
            // 初期化
            updateSourceLanguages();
            
            // 翻訳サービスの変更時に言語リストを更新
            translationService.on("change", updateSourceLanguages);
        });
        </script>';
    }
    
    /**
     * Render similarity threshold field
     */
    public function render_similarity_threshold_field() {
        $threshold = get_option('s2j_slug_generater_similarity_threshold', 80);
        echo '<input type="range" name="s2j_slug_generater_similarity_threshold" id="s2j_slug_generater_similarity_threshold" min="0" max="100" step="10" value="' . esc_attr($threshold) . '" />';
        echo '<span id="s2j_slug_generater_threshold_value">' . esc_html($threshold) . '%</span>';
        echo '<p class="description">' . __('Set the minimum similarity threshold for slug candidates.', 's2j-slug-generater') . '</p>';
    }
    
    /**
     * Get plan selection URL for the selected service
     */
    private function get_plan_selection_url($service) {
        switch ($service) {
            case 'deepl':
                return 'https://www.deepl.com/pro-api#api-pricing';
            case 'google':
                return 'https://cloud.google.com/translate/pricing';
            default:
                return '#';
        }
    }
    
    /**
     * Get API key URL for the selected service
     */
    private function get_api_key_url($service) {
        switch ($service) {
            case 'deepl':
                return 'https://www.deepl.com/ja/pro#developer';
            case 'google':
                return 'https://cloud.google.com/translate/docs/setup';
            default:
                return '#';
        }
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        echo '<div class="wrap">';
        echo '<h1>' . esc_html(get_admin_page_title()) . '</h1>';
        echo '<form action="options.php" method="post">';
        
        settings_fields('s2j_slug_generater_options');
        do_settings_sections('s2j-slug-generater');
        submit_button();
        
        echo '</form>';
        echo '</div>';
        
        // Add JavaScript for threshold slider
        echo '<script>
            jQuery(document).ready(function($) {
                $("#s2j_slug_generater_similarity_threshold").on("input", function() {
                    $("#s2j_slug_generater_threshold_value").text($(this).val() + "%");
                });
                
                $("#s2j_slug_generater_translation_service").on("change", function() {
                    var service = $(this).val();
                    var planUrl = "' . admin_url('admin-ajax.php') . '";
                    var data = {
                        action: "s2j_get_service_urls",
                        service: service,
                        nonce: "' . wp_create_nonce('s2j_service_urls_nonce') . '"
                    };
                    
                    $.post(planUrl, data, function(response) {
                        if (response.success) {
                            var description = response.data.description;
                            $("#s2j_slug_generater_translation_service").next("p.description").html(description);
                        }
                    });
                });
            });
        </script>';
    }
}
