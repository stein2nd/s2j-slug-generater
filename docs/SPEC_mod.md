# s2j-slug-generater SPEC (Modified)

## 1. はじめに

本ドキュメントでは、WordPress プラグイン「s2j-slug-generater」の専用仕様を定義します。
本プラグインの設計は、以下の共通 SPEC に準拠します。

- [WP_PLUGIN_SPEC.md (共通仕様)](https://github.com/stein2nd/wp-plugin-spec/blob/main/WP_PLUGIN_SPEC.md)

以下は、本プラグイン固有の仕様をまとめたものです。

本ドキュメントは、`SPEC.md` をもとに、以下の変更を加えたものです。

1. **類似度判定の仕様変更**: レーベンシュタイン距離から、[S2J Similarity Service](https://github.com/stein2nd/s2j-similarity-service) を利用したコサイン類似度判定に変更
2. **依存関係の更新**: npm モジュールのバージョンを「S2J Alliance Manager」と統一

### docs/SPEC.md と docs/SPEC_mod.md の主な差分 (簡単要約)

- **類似度ロジックの変更**  
  - `SPEC.md`: レーベンシュタイン距離で0〜100のスコアを計算。  
  - `SPEC_mod.md`: [S2J Similarity Service](https://github.com/stein2nd/s2j-similarity-service) を使った「コサイン類似度 (0.0〜1.0)」に変更し、パーセント表示に変換して利用。

- **設定画面の項目追加・変更**  
  - 項目を新規追加 (「OpenAI API キー」「OpenAI モデル名」「ロケール」)。  
  - 類似度閾値スライダー: `0〜100` → `0.0〜1.0` (表示は0〜100%) に仕様変更。

- **依存関係と構成の拡張**  
  - フォルダー構成に `composer.json` を追加し、PHP 依存として `stein2nd/s2j-similarity-service` を導入する指定。  
  - `package.json` の `dependencies` / `devDependencies` を「S2J Alliance Manager」と統一するための具体的なバージョン一覧を追記。

- **実装ガイドの追記**  
  - PHP 側 (`includes/SlugGenerater.php`) で Composer autoload 追加、類似度計算メソッドの差し替えなどの実装修正ポイントを詳しく記載。  
  - 設定画面 / フロントエンド (`SettingsPage.php`, `src/admin/index.tsx` など) の具体的な修正ポイントを追加。

- **REST API 仕様の拡張**  
  - `SPEC.md`: 入力 `title`、出力は `candidates` の配列のみ。  
  - `SPEC_mod.md`: 入力に `nonce` を明示、出力を `success` / `data` ラッパー形式に変更し、`similarity` (パーセント) と `translated_title` を追加。

- **メタ情報の追加**  
  - `SPEC_mod.md` 冒頭で「どこをどう変更した SPEC なのか」を明示。  
  - 末尾に変更履歴 (`v1.0.0 (Modified)`) を追加。

---

## 2. プラグイン概要

* 名称: S2J Slug Generater
* プラグイン・スラッグ: s2j-slug-generater
* テキスト・ドメイン: s2j-slug-generater
* ライセンス: GPL v2以降
* 目的: 投稿タイトルをもとに、翻訳サービス提供の API を利用して翻訳し、最適なスラッグ候補を自動生成します。
* 特徴:
  * Gutenberg ブロックエディターに対応します (Post Title 直下に Slot として追加)。
  * MetaBox により、Classic エディターに対応します。
    * Gutenberg ブロック同様、Post Title 直下に MetaBox を追加します。
    * Gutenberg ブロックでの処理内容を基本的に再現します。
  * 管理画面で API キーや翻訳言語の設定を保存します。
  * 類似度チェック機能により、候補のフィルタリングが可能です ([S2J Similarity Service](https://github.com/stein2nd/s2j-similarity-service) を使用)。

## 3. プロジェクト構成

### 3.1. フォルダー構成

```
s2j-slug-generater/
├─ `package.json` # ビルド設定
├─ `SPEC.md` # プラグイン固有仕様
├─ `vite.config.ts`
├─ `tsconfig.json`
├─ `LICENSE`
├─ `readme.md`
├─ `s2j-slug-generater.php` # プラグイン本体
├─ `uninstall.php` # プラグイン削除時の処理
├─ `composer.json` # PHP依存関係 (S2J Similarity Service を含む)
├─ includes/ # PHP クラス群 (REST、Settings、Admin UI)
│　├─ `SettingsPage.php` (設定画面)
│　├─ `RestController.php`
│　├─ `SlugGenerater.php` (Gutenberg ブロック)
│　└─ ...
├─ src/ # TypeScript/React (Gutenberg ブロック、設定画面) /SCSS ソース
│　├─ admin/ # 設定画面用
│　│　└─ ...
│　├─ gutenberg/ # Gutenberg ブロック用
│　│　└─ ...
│　├─ classic/ # MetaBox 用
│　│　└─ ...
│　├─ styles/ # プラグイン用のスタイル定義
│　│　├─ `admin.scss` (設定画面用)
│　│　├─ `gutenberg.scss` (Gutenberg ブロック用)
│　│　├─ `classic.scss` (MetaBox 用)
│　│　└─ ...
│　└─ types/ # プラグイン用のグローバル・タイプ・定義
│　　　└─ ...
├─ dist/ # Vite ビルド成果物 (Git 管理外)、アイコン
│　├─ js/ # プラグイン用のGutenberg ブロック、設定画面
│　│　└─ ...
│　└─ css/ # プラグイン用のスタイル定義
│　　　└─ ...
└─ languages/ # 翻訳ファイル (.pot、.po、.mo)
```

### 3.2. 主要ファイル

* `s2j-slug-generater.php` : プラグイン起点、クラスロード・初期化
* `includes/SettingsPage.php` : 管理画面の設定フォーム
* `includes/RestController.php` : REST API エンドポイント定義
* `includes/SlugGenerater.php` : 翻訳サービス提供の API 呼び出し・候補生成ロジック
* `src/gutenberg/index.tsx` : Gutenberg ブロックの UI ロジック
* `src/classic/index.ts` : Classic エディター対応スクリプト

---

## 4. ビルド要件

* Vite + TypeScript + SCSS
  * `vite.config.ts` を用いて IIFE 形式でバンドルする
  * JavaScript は WordPress 同梱の jQuery を利用可能とする (外部 import 不要) (`jQuery(function($) { ... })`)
  * CSS も IIFE 出力し、エディター用・フロント用を区別すること
* 出力は `./dist`

### 4.1. `package.json` の `scripts`

* `npm run build:dev` → 開発用ビルド (minify 無効)
* `npm run build:production` → 本番用ビルド (minify 有効)

### 4.2. `package.json` の依存関係

以下の npm モジュールのバージョンを「S2J Alliance Manager」と統一します。

```json
{
  "dependencies": {
    "react": "^19.2.0",
    "react-dom": "^19.2.0"
  },
  "devDependencies": {
    "@types/jquery": "^3.5.33",
    "@types/node": "^24.9.2",
    "@types/react": "^18.2.0",
    "@types/react-dom": "^18.2.0",
    "@typescript-eslint/eslint-plugin": "^8.46.2",
    "@typescript-eslint/parser": "^8.46.2",
    "@wordpress/api-fetch": "^7.29.0",
    "@wordpress/block-editor": "^15.2.0",
    "@wordpress/blocks": "^15.2.0",
    "@wordpress/components": "^30.2.0",
    "@wordpress/data": "^10.29.0",
    "@wordpress/element": "^6.29.0",
    "@wordpress/i18n": "^6.2.0",
    "@wordpress/scripts": "^30.22.0",
    "@wordpress/url": "^4.29.0",
    "autoprefixer": "^10.4.21",
    "eslint": "^9.38.0",
    "eslint-plugin-react": "^7.37.5",
    "eslint-plugin-react-hooks": "^7.0.1",
    "rollup": "^4.52.5",
    "sass": "^1.93.2",
    "stylelint": "^16.25.0",
    "stylelint-config-standard-scss": "^16.0.0",
    "typescript": "^5.9.3",
    "vite": "^7.1.12",
    "vite-plugin-static-copy": "^3.1.4"
  }
}
```

### 4.3. PHP 依存関係

* [S2J Similarity Service](https://github.com/stein2nd/s2j-similarity-service) を Composer 経由で導入します。
* `composer.json` に追加する必要があります。

```json
{
  "require": {
    "stein2nd/s2j-similarity-service": "^1.0.0"
  }
}
```

## 5. 国際化

* テキストはすべて `__()` または `_e()` を使用
* 翻訳ファイルは `languages/` に配置
* 翻訳テンプレート `.pot` は `makepot` により生成
* Text Domain は plugin-slug に合わせる

## 6. 固有仕様

### 6.1. 翻訳フローと、翻訳サービス提供の API の関係

* GoF の『デザインパターン』「Template Method」パターンに準拠した形で、翻訳フローを構築します。
* GoF の『デザインパターン』「Abstract Factory」パターンに於ける「Factory」として、各翻訳サービス API を利用します。
  * 具体 Factory では、下記項目を規定します。
    * 提示リンク
      * 「プラン選択ページ」へのリンク
      * 「無料枠 API キーの取得ページ」へのリンク
      * これらは `sprintf(__('Go to <a target="_blank" href="%1$s">the API plan selection page</a> and <a target="_blank" href="%2$s">obtain a free API key</a>.'), xxxxx, yyyyy)` の形で、管理画面で提示します。
    * 利用エンドポイント (無料枠のもの)
    * 設定可能な項目:
      * API キー
      * 翻訳元言語 (例: ja)
      * 類似度閾値 (例: 0.8、コサイン類似度として0.0〜1.0で設定)

#### 6.1.1. DeepL API

* 提示リンク
  * 「プラン選択ページ」へのリンク: `https://www.deepl.com/pro-api#api-pricing`
  * 「無料枠 API キーの取得ページ」へのリンク: `https://www.deepl.com/ja/pro#developer`
* 利用エンドポイント: `https://api-free.deepl.com/v2/translate`
* 設定可能な項目:
  * API キー
  * 翻訳元言語 (例: ja)
  * 類似度閾値 (例: 0.8)

#### 6.1.2. Google 翻訳 API

* 提示リンク
  * 「プラン選択ページ」へのリンク: `https://cloud.google.com/translate/pricing?hl=ja`
  * 「無料枠 API キーの取得ページ」へのリンク: `https://cloud.google.com/translate/docs/setup?hl=ja`
* 利用エンドポイント: `https://translation.googleapis.com/language/translate/v2`
* 設定可能な項目:
  * API キー
  * 翻訳元言語 (例: ja)
  * 類似度閾値 (例: 0.8)

### 6.2. 管理画面

* 設定ページに以下を用意します:
  * 翻訳 API 選択ドロップダウン
  * API キー入力欄
    * 選択した「翻訳 API」に対応した、「無料枠 API キーの取得ページ」への案内リンクを添えます。
  * 翻訳元の言語選択ドロップダウン
    * 選択された翻訳サービスに応じて、jQuery の DOM 操作により、動的に選択肢が切り替わります。
    * 選択肢
      * 言語コードは、各翻訳サービスの API 仕様に準拠した形式です。
      * デフォルト言語は、日本語 (ja) です。
  * **OpenAI API キー入力欄** (新規追加)
    * S2J Similarity Service で使用する OpenAI API キーを設定します。
    * 案内リンク: `https://platform.openai.com/api-keys`
  * **OpenAI モデル名入力欄** (新規追加)
    * S2J Similarity Service で使用する埋め込みモデル名を設定します。
    * デフォルト値: `text-embedding-3-small`
    * 選択肢の例: `text-embedding-3-small`, `text-embedding-3-large`, `text-embedding-ada-002`
  * **ロケール設定** (新規追加)
    * S2J Similarity Service で使用するロケールを設定します。
    * デフォルト値: `ja_JP`
    * 選択肢の例: `ja_JP`, `en_US`, `fr_FR` など
  * 類似度の閾値設定スライダー
    * min: 0.0、max: 1.0、step: 0.1
    * デフォルト値: 0.8
    * 表示形式: パーセント表示 (0.8→80%)
* 保存は `update_option` / `get_option` を利用します。

### 6.3. Gutenberg ブロック対応

* Post Title 直下に Slot として追加します。
* 下記 HTML 要素を内包します。
  * ボタン「候補生成」
  * テキストボックス「スラッグ候補」
  * ラベル「類似度」
    * 右横に ` %` を添えます。
  * ボタン「スラッグ化」

### 6.4. Classic エディター対応

* Gutenberg ブロック同様、Post Title 直下に Slot として追加します。
* 下記 HTML 要素を内包します。
  * ボタン「候補生成」
  * テキストボックス「スラッグ候補」
  * ラベル「類似度」
    * 右横に ` %` を添えます。
  * ボタン「スラッグ化」

### 6.5. スラッグ候補の生成ロジック

* Gutenberg ブロック、MetaBox から呼ばれます。

1. ボタン「候補生成」クリック時
    1. `get_the_title()` または `the_title_attribute()` を利用してタイトル入力を取得します。
    2. 翻訳 API で英語に翻訳します。
      * 翻訳結果は、テキストボックス「スラッグ候補」にセットします。
    3. 「1.2」で翻訳したものを、翻訳 API で、設定値「翻訳元言語」へと、逆翻訳します。
      * この翻訳結果は、テキストボックスにセットする必要はありません。
    4. **類似度判定 (変更) **: 元のタイトル (基準テキスト) と「1.3」で逆翻訳したテキスト (検証テキスト) を、[S2J Similarity Service](https://github.com/stein2nd/s2j-similarity-service) を使用して比較します。
      * `SimilarityService(new OpenAIEmbeddingStrategy())` の `compare()` メソッドを呼び出します。
      * パラメータ:
        * `apiKey`: 設定値「OpenAI API キー」
        * `model`: 設定値「OpenAI モデル名」(デフォルト: `text-embedding-3-small`)
        * `baseText`: 元のタイトル
        * `targetText`: 逆翻訳したテキスト
        * `language`: 設定値「翻訳元言語」(例: `ja`)
        * `locale`: 設定値「ロケール」(デフォルト: `ja_JP`)
      * 戻り値 `$result['similarity']` でコサイン類似度 (0.0〜1.0) を取得します。
      * コサイン類似度をパーセント (0.0〜100.0) に変換します (例: 0.85→85.0)。
      * 比較結果をラベル「類似度」にセットします。
      * ラベルにセットする際は、`n.99` の形に整形します (小数部第2位まで表示)。
      * 設定値「類似度閾値」と比較し、閾値未満の場合は候補をフィルタリングします。
2. ボタン「スラッグ化」クリック時
    1. テキストボックス「スラッグ候補」の文字列を、記号・スペースを正規化してスラッグ形式に変換します。
    2. スラッグ欄に設定します。

### 6.6. 実装修正のポイント

#### 6.6.1. PHP 実装 (`includes/SlugGenerater.php`)

1. **Composer の autoload を追加**
   ```php
   require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
   ```

2. **類似度計算メソッドの変更**
   * `calculate_similarity()` メソッドを削除または置き換え
   * 新しく `calculate_similarity_with_service()` メソッドを追加:
   ```php
   private function calculate_similarity_with_service($base_text, $target_text) {
       $openai_api_key = get_option('s2j_slug_generater_openai_api_key', '');
       $model = get_option('s2j_slug_generater_openai_model', 'text-embedding-3-small');
       $source_language = get_option('s2j_slug_generater_source_language', 'ja');
       $locale = get_option('s2j_slug_generater_locale', 'ja_JP');
       
       if (empty($openai_api_key)) {
           throw new Exception(__('OpenAI API key is not configured.', 's2j-slug-generater'));
       }
       
       $strategy = new \S2J\SimilarityService\OpenAIEmbeddingStrategy();
       $similarity_service = new \S2J\SimilarityService\SimilarityService($strategy);
       
       $result = $similarity_service->compare(
           $openai_api_key,
           $model,
           $base_text,
           $target_text,
           $source_language,
           $locale
       );
       
       // コサイン類似度 (0.0〜1.0) をパーセント (0.0〜100.0) に変換
       return $result['similarity'] * 100;
   }
   ```

3. **`generate_candidates()` メソッドの修正**
   * `calculate_similarity()` の呼び出しを `calculate_similarity_with_service()` に変更
   * 類似度閾値の比較ロジックを修正 (0.0〜1.0の値と比較するように)

#### 6.6.2. 設定画面 (`includes/SettingsPage.php`)

1. **設定項目の新規追加**
   * OpenAI API キー入力欄
   * OpenAI モデル名選択ドロップダウン
   * ロケール選択ドロップダウン

2. **類似度閾値スライダーの修正**
   * min: 0.0、max: 1.0、step: 0.1に変更
   * 表示をパーセント形式に変換 (0.8→80%)

#### 6.6.3. フロントエンド実装

1. **類似度表示の修正**
   * コサイン類似度 (0.0〜1.0) をパーセント (0.0〜100.0) に変換して表示

2. **設定画面 (`src/admin/index.tsx`)**
   * OpenAI API キー入力欄を追加
   * OpenAI モデル名選択ドロップダウンを追加
   * ロケール選択ドロップダウンを追加
   * 類似度閾値スライダーの範囲を0.0〜1.0に変更

## 7. REST API 仕様

### 7.1. エンドポイント

* `POST /wp-json/s2j-slug-generater/v1/generate`
  * 入力: `{ title: "記事タイトル", nonce: "nonce文字列" }`
  * 出力: `{ success: true, data: { candidates: [ "slug-1", "slug-2" ], similarity: 85.5, translated_title: "translated title" } }`
  * 類似度はパーセント形式 (0.0〜100.0) で返却されます。

### 7.2. セキュリティ

* nonce チェック必須
* `current_user_can( 'edit_posts' )` 権限がある場合のみ利用可

## 8. 今後の拡張

* 他の翻訳 API (Azure Translator) への対応
* スラッグ候補のランキング付け
* CLI コマンド (wp-cli) 連携
* 他の埋め込み戦略 (OpenAI 以外) への対応

---

## 変更履歴

* **v1.0.0 (Modified)**: 類似度判定を S2J Similarity Service を使用する形に変更、npm モジュールのバージョン統一
