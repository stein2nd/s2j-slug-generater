# s2j-slug-generater SPEC

## 1. はじめに

本ドキュメントでは、WordPress プラグイン「s2j-slug-generater」の専用仕様を定義します。
本プラグインの設計は、以下の共通 SPEC に準拠します。

- [WP_PLUGIN_SPEC.md (共通仕様)](https://github.com/stein2nd/wp-plugin-spec/blob/main/WP_PLUGIN_SPEC.md)

以下は、本プラグイン固有の仕様をまとめたものです。

---

## 2. プラグイン概要

* 名称: S2J Slug Generater
* プラグイン・スラッグ: s2j-slug-generater
* テキスト・ドメイン: s2j-slug-generater
* ライセンス: GPL v2 以降
* 目的: 投稿タイトルをもとに、翻訳サービス提供の API を利用して翻訳し、最適なスラッグ候補を自動生成します。
* 特徴:
  * Gutenberg ブロックエディタに対応します (Post Title 直下に Slot として追加)。
  * MetaBox により、Classic エディタに対応します。
    * Gutenberg ブロック同様、Post Title 直下に MetaBox を追加します。
    * Gutenberg ブロックでの処理内容を基本的に再現します。
  * 管理画面で API キーや翻訳言語設定を保存します。
  * 類似度チェック機能により、候補のフィルタリングが可能です。

## 3. プロジェクト構成

### 3.1 フォルダ構成

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

### 3.2 主要ファイル

* `s2j-slug-generater.php` : プラグイン起点、クラスロード・初期化
* `includes/SettingsPage.php` : 管理画面の設定フォーム
* `includes/RestController.php` : REST API エンドポイント定義
* `includes/SlugGenerater.php` : 翻訳サービス提供の API 呼び出し・候補生成ロジック
* `src/gutenberg/index.tsx` : Gutenberg ブロックの UI ロジック
* `src/classic/index.ts` : Classic エディタ対応スクリプト

---

## 4. ビルド要件

* Vite + TypeScript + SCSS
  * `vite.config.ts` を用いて IIFE 形式でバンドルする
  * JavaScript は WordPress 同梱の jQuery を利用可能とする （外部 import 不要） (`jQuery(function($) { ... })`)
  * CSS も IIFE 出力し、エディタ用・フロント用を区別すること
* 出力は `./dist`

### 4.1 `package.json` の `scripts`

* `npm run build:dev` → 開発用ビルド（minify 無効）
* `npm run build:production` → 本番用ビルド（minify 有効）

## 5. 国際化

* テキストはすべて `__()` または `_e()` を使用
* 翻訳ファイルは `languages/` に配置
* 翻訳テンプレート `.pot` は `makepot` により生成
* Text Domain は plugin-slug に合わせる

## 6. 固有仕様

### 6.1 翻訳フローと、翻訳サービス提供の API の関係

* GoF 『デザインパターン』「Template Method」パターンに準拠した形で、翻訳フローを構築します。
* GoF 『デザインパターン』「Abstract Factory」パターンに於ける「Factory」として、各翻訳サービス API を利用します。
  * 具体 Factory では、下記項目を規定します。
    * 提示リンク
      * 「プラン選択ページ」へのリンク
      * 「無料枠 API キーの取得ページ」へのリンク
      * これらは `sprintf(__('Go to <a target="_blank" href="%1$s">the API plan selection page</a> and <a target="_blank" href="%2$s">obtain a free API key</a>.'), xxxxx, yyyyy)` の形で、管理画面で提示します。
    * 利用エンドポイント (無料枠のもの)
    * 設定可能項目:
      * API キー
      * 翻訳元言語（例: ja）
      * 類似度閾値（例: 80）

#### 6.1.1 DeepL API

* 提示リンク
  * 「プラン選択ページ」へのリンク: `https://www.deepl.com/pro-api#api-pricing`
  * 「無料枠 API キーの取得ページ」へのリンク: `https://www.deepl.com/ja/pro#developer`
* 利用エンドポイント: `https://api-free.deepl.com/v2/translate`
* 設定可能項目:
  * API キー
  * 翻訳元言語（例: ja）
  * 類似度閾値（例: 80）

#### 6.1.2 Google 翻訳 API

* 提示リンク
  * 「プラン選択ページ」へのリンク: `https://cloud.google.com/translate/pricing?hl=ja`
  * 「無料枠 API キーの取得ページ」へのリンク: `https://cloud.google.com/translate/docs/setup?hl=ja`
* 利用エンドポイント: `https://translation.googleapis.com/language/translate/v2`
* 設定可能項目:
  * API キー
  * 翻訳元言語（例: ja）
  * 類似度閾値（例: 80）

### 6.2 管理画面

* 設定ページに以下を用意します:
  * 翻訳 API 選択ドロップダウン
  * API キー入力欄
    * 選択した「翻訳 API」に対応した、「無料枠 API キーの取得ページ」への案内リンクを添えます。
  * 翻訳元の言語選択ドロップダウン
    * `wp_dropdown_languages` を利用します。
    * 条件配列
      * `languages` には、`get_available_languages()` をセットします。
      * `translations` には、`wp_get_available_translations()` をセットします (`require_once ABSPATH . 'wp-admin/includes/translation-install.php'` を事前に実施)。
      * `selected` には、`get_option(翻訳元, get_locale())` をセットします。
      * `show_available_translations` には、`current_user_can('install_languages') && \wp_can_install_language_pack()` をセットします。
  * 類似度の閾値設定スライダー
    * min: 0、max: 100、step: 10
* 保存は `update_option` / `get_option` を利用します。

### 6.3 Gutenberg ブロック対応

* Post Title 直下に Slot として追加します。
* 下記 HTML 要素を内包します。
  * ボタン「候補生成」
  * テキストボックス「スラッグ候補」
  * ラベル「類似度」
    * 右横に「 %」を添えます。
  * ボタン「スラッグ化」

### 6.4 Classic エディタ対応

* Gutenberg ブロック同様、Post Title 直下に Slot として追加します。
* 下記 HTML 要素を内包します。
  * ボタン「候補生成」
  * テキストボックス「スラッグ候補」
  * ラベル「類似度」
    * 右横に「 %」を添えます。
  * ボタン「スラッグ化」

### 6.5 スラッグ候補の生成ロジック

* Gutenberg ブロック、MetaBox から呼ばれます。

1. ボタン「候補生成」クリック時
    1. タイトル入力を取得します。
    2. 翻訳 API で英語に翻訳します。
      * 翻訳結果は、テキストボックス「スラッグ候補」にセットします。
    3. 「1.2」で翻訳したものを、翻訳 API で、設定値「翻訳元言語」へと、逆翻訳します。
      * この翻訳結果は、テキストボックスにセットする必要はありません。
    4. 「1.2」で翻訳したものと「1.3」で逆翻訳したものを、設定値「類似度閾値」に基づいて、比較 (レーベンシュタイン距離を利用) します。
      * 比較結果をラベル「類似度」にセットします。
      * ラベルにセットする際は、`n.99` の形に整形します (小数部第２位まで表示)。
2. ボタン「スラッグ化」クリック時
    1. テキストボックス「スラッグ候補」の文字列を、記号・スペースを正規化してスラッグ形式に変換します。
    2. スラッグ欄に設定します。

## 7. REST API 仕様

### 7.1 エンドポイント

* `POST /wp-json/s2j-slug-generater/v1/generate`
  * 入力: `{ title: "記事タイトル" }`
  * 出力: `{ candidates: [ "slug-1", "slug-2" ] }`

### 7.2 セキュリティ

* nonce チェック必須
* `current_user_can( 'edit_posts' )` 権限がある場合のみ利用可

## 8. 今後の拡張

* 他の翻訳 API（Azure Translator）への対応
* スラッグ候補のランキング付け
* CLI コマンド (wp-cli) 連携

---
