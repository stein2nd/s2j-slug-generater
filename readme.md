# S2J Slug Generater

[![License: GPL v3](https://img.shields.io/badge/License-GPL%20v3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0.en.html)
[![Vite](https://img.shields.io/badge/vite-8.2-blue.svg)](https://vite.dev)
[![PHP](https://img.shields.io/badge/PHP-8.2-blue.svg)](https://www.php.net/)
[![WordPress](https://img.shields.io/badge/WordPress-6.3+-blue.svg)](https://wordpress.org/)

A WordPress plugin that generates a slug candidate from a post title via translation APIs and embedding cosine similarity. Supports Gutenberg and Classic editor.

投稿タイトルを翻訳 API と埋め込みのコサイン類似度で処理し、スラッグ候補を生成する WordPress プラグインです。Gutenberg と Classic エディターの両方に対応します。

## Features

* **Translation API integration**: DeepL or Google Translate to English, then reverse-translate for a round-trip check

  **翻訳 API 連携**: DeepL または Google 翻訳で英訳し、逆翻訳でラウンドトリップ検査します。

* **Cosine similarity**: Compares the original title with the reverse translation via [`s2j/similarity-service`](https://packagist.org/packages/s2j/similarity-service) (built-in provider: OpenAI embeddings)

  **コサイン類似度**: [`s2j/similarity-service`](https://packagist.org/packages/s2j/similarity-service) で元タイトルと逆翻訳を比較します (組込みプロバイダは OpenAI 埋め込みです)。

* **Single candidate**: Returns the English translation as one editable candidate. Slug normalization runs in the editor on **Slugify**

  **候補は1件**: 英訳テキストを編集可能な候補として返します。スラッグ正規化はエディターの **Slugify** で行います。

* **Dual editor support**: Gutenberg document sidebar (`PluginDocumentSettingPanel`) and Classic panel directly under the title

  **両エディター対応**: Gutenberg は文書設定サイドバー (`PluginDocumentSettingPanel`)、Classic はタイトル直下のパネルです。

* **Configurable settings**: Translation key / plan / language, similarity AI key / model, and a ratio threshold (shown as %)

  **設定可能**: 翻訳キー / プラン / 言語、類似度 AI キー / モデル、比率の閾値 (表示は %) です。

* **Modern UI**: Gutenberg uses WordPress components; Classic uses a jQuery panel with the same domain operations

  **UI**: Gutenberg は WordPress コンポーネント、Classic は同じドメイン操作の jQuery パネルです。

## Requirements

* WordPress v6.3+ (tested up to v6.8)

  WordPress v6.3以上です (v6.8まで検証済み)。

* PHP v8.2+

  PHP v8.2以上です。

* Composer (runtime: `vendor/` from `composer install`)

  Composer が必要です。実行時は `composer install` で `vendor/` を用意します。

* Node.js v20+ (for development)

  開発時は Node.js v20以上です。

## Installation

1. Place the plugin folder in `/wp-content/plugins/`

   プラグインフォルダーを `/wp-content/plugins/` に配置します。

2. From a Git checkout, install PHP dependencies (`vendor/` is not in Git):

   Git チェックアウトの場合は PHP 依存を入れます (`vendor/` は Git 管理外です)。

   ```bash
   composer install
   ```

3. Activate the plugin in **Plugins**

   **プラグイン** 画面で有効化します。

4. Open **Settings → S2J Slug Generater** and enter both API keys (translation and similarity AI)

   **設定 → S2J Slug Generater** を開き、翻訳用と類似度 AI 用の両方の API キーを入力します。

Release packages are expected to include `vendor/`. A Git clone does not.

リリース成果物には `vendor/` を同梱する想定です。Git クローンには含まれません。

## Configuration

### Translation API

1. **DeepL**:
   * [API plan pricing](https://www.deepl.com/pro-api#api-pricing)
   * [Obtain an API key](https://www.deepl.com/ja/pro#developer)
   * Plan: `auto` (try Pro host then Free) / `free` / `pro`

   **DeepL**:
   * [API プランの料金](https://www.deepl.com/pro-api#api-pricing)
   * [API キーの取得](https://www.deepl.com/ja/pro#developer)
   * プラン: `auto` (Pro ホストを試し、失敗時は Free) / `free` / `pro`

2. **Google Translate**:
   * [Pricing](https://cloud.google.com/translate/pricing?hl=ja)
   * [Setup guide](https://cloud.google.com/translate/docs/setup?hl=ja)

   **Google 翻訳**:
   * [料金](https://cloud.google.com/translate/pricing?hl=ja)
   * [セットアップ案内](https://cloud.google.com/translate/docs/setup?hl=ja)

### Similarity AI (OpenAI embeddings)

This key is **separate from the translation API key**. Use [OpenAI API Platform](https://platform.openai.com/) — not the ChatGPT app login.

このキーは **翻訳 API キーとは別** です。[OpenAI API Platform](https://platform.openai.com/) を使います。ChatGPT アプリのログインではありません。

* [Sign up](https://platform.openai.com/signup)
* [Billing / credits](https://platform.openai.com/settings/organization/billing)
* [API keys](https://platform.openai.com/api-keys)

* [アカウント登録](https://platform.openai.com/signup)
* [課金 / クレジット](https://platform.openai.com/settings/organization/billing)
* [API キー](https://platform.openai.com/api-keys)

Default model: `text-embedding-3-small`. Credits are required; a key with zero balance will fail comparison.

デフォルトモデルは `text-embedding-3-small` です。クレジットが必要です。残高0のキーでは比較に失敗します。

### Plugin settings

**Translation Service Settings**

**翻訳サービス設定**

* **Translation Service**: DeepL or Google Translate

  **翻訳サービス**: DeepL または Google 翻訳です。

* **Translation API Key**: text field (`autocomplete="off"`)

  **翻訳 API キー**: テキスト欄です (`autocomplete="off"`)。

* **DeepL API Plan**: `auto` / `free` / `pro` (used when DeepL is selected)

  **DeepL API プラン**: `auto` / `free` / `pro` です。DeepL 選択時に使います。

* **Source Language**: language of post titles (default `ja`)

  **翻訳元言語**: 投稿タイトルの言語です (デフォルトは `ja`)。

**Similarity Settings**

**類似度設定**

* **Similarity AI Service**: built-in `openai`

  **類似度 AI サービス**: 組込みは `openai` です。

* **Similarity AI API Key**: embedding key (not the translation key)

  **類似度 AI API キー**: 埋め込み用です (翻訳キーではありません)。

* **Similarity AI Model**: from the provider descriptor (default `text-embedding-3-small`)

  **類似度 AI モデル**: プロバイダ記述子の一覧です (デフォルトは `text-embedding-3-small`)。

* **Locale**: stored for future use (default `ja_JP`; not sent to the current similarity API)

  **ロケール**: 将来用に保持します (デフォルトは `ja_JP`。現行の類似度 API には渡しません)。

* **Similarity Threshold**: range `0.0`–`1.0` / increment `0.1` / default `0.8` displayed as percent. Legacy percent values (`80`) are converted to ratio (`0.8`) on load

  **類似度閾値**: 範囲 `0.0`–`1.0` / 刻み `0.1` / デフォルト `0.8` で、表示はパーセントです。旧来の percent 値 (`80`) は読み込み時に ratio (`0.8`) へ変換します。

## Usage

### Gutenberg editor

1. Create or edit a post and enter a title

   投稿を作成または編集し、タイトルを入力します。

2. Open the document settings sidebar and find **S2J Slug Generater**

   文書設定サイドバーを開き、**S2J Slug Generater** を探します。

3. Click **Generate Candidates**

   **Generate Candidates** をクリックします。

4. Review **Slug Candidate** (editable English text) and **Similarity**

   **Slug Candidate** (編集可能な英訳) と **Similarity** を確認します。

5. Click **Slugify** to apply via `editPost({ slug })`. The button is disabled when `accepted` is false (below threshold)

   **Slugify** で `editPost({ slug })` により適用します。`accepted` が false (閾値未満) のときはボタンが無効です。

6. **Current Slug** shows the value from the editor store

   **Current Slug** はエディター store の値を表示します。

### Classic editor

1. Create or edit a post and enter a title

   投稿を作成または編集し、タイトルを入力します。

2. Use the **S2J Slug Generater** panel directly under the title (not a metabox below the content)

   タイトル直下の **S2J Slug Generater** パネルを使います (コンテンツ下のメタボックスではありません)。

3. Click **Generate Candidates**

   **Generate Candidates** をクリックします。

4. Review the candidate and similarity

   候補と類似度を確認します。

5. Click **Slugify** to write `#post_name` / `#editable-post-name`. Disabled when below threshold

   **Slugify** で `#post_name` / `#editable-post-name` に書き込みます。閾値未満では無効です。

## How it works

1. **Translate**: title `sourceLanguage` → English

   **翻訳**: タイトルを `sourceLanguage` から英語へ訳します。

2. **Reverse translate**: English → `sourceLanguage`

   **逆翻訳**: 英語から `sourceLanguage` へ戻します。

3. **Similarity**: cosine similarity of the original title vs reverse translation (OpenAI embeddings via `s2j/similarity-service`)

   **類似度**: 元タイトルと逆翻訳のコサイン類似度です (OpenAI 埋め込み。`s2j/similarity-service` 経由)。

4. **Threshold**: ratio ≥ setting → `accepted: true`. Below threshold still returns `HTTP 200` with the score; Slugify stays disabled

   **閾値**: 比率が設定以上なら `accepted: true` です。閾値未満でもスコア付きの `HTTP 200` を返し、Slugify は無効のままです。

5. **Slugify** (client): `normalizeToSlug` on the candidate text, then apply to the post slug

   **Slugify** (クライアント): 候補テキストを `normalizeToSlug` し、投稿スラッグへ適用します。

API keys stay in WordPress Options and are never returned in REST responses.

API キーは WordPress の Option にとどめ、REST レスポンスには含めません。

## Development

### Building from source

```bash
composer install
npm install

npm run build:dev
npm run build:production
npm run dev
npm run type-check
npm run lint
npm run lint:docs
```

`npm install` reads `.npmrc` for `legacy-peer-deps=true` and `allow-git=all`. Do not pass `--force` unless you have a separate reason.

`npm install` は `.npmrc` の `legacy-peer-deps=true` と `allow-git=all` を使います。別の理由がなければ `--force` は付けないでください。

### Project structure

```
s2j-slug-generater/
├── includes/              # PHP (FOP outer frame + domain)
│   ├── Admin/             # Settings API
│   ├── Config/            # Option Adapter
│   ├── Domain/            # Pure functions
│   ├── Editor/            # Gutenberg / Classic mounts
│   ├── Pipeline/          # generate_candidate orchestrator
│   ├── Providers/         # Translation registry (deepl / google)
│   ├── Similarity/        # Similarity AI registry (openai)
│   └── RestController.php
├── src/
│   ├── admin/             # Settings assets (styles)
│   ├── classic/           # Title-below panel
│   ├── domain/            # Shared panel mapping / normalizeToSlug
│   ├── gutenberg/         # PluginDocumentSettingPanel UI
│   ├── styles/
│   └── types/
├── dist/                  # Built assets (not in Git)
├── vendor/                # Composer (not in Git)
├── languages/
├── docs/                  # Adopted spec
└── docs_mod/              # Spec drafts
```

PHP 側は FOP の外枠とドメイン、`src/` は管理画面 ・ Classic ・ Gutenberg の UI です。`dist/` と `vendor/` は Git 管理外です。`docs/` が採用仕様、`docs_mod/` は検討用ドラフトです。

### Architecture

* **FOP + Clean Coding**: outer adapters / facades for WordPress, HTTP, and editor I/O; inner pure functions for validation, percent, threshold, and slug normalization

  **FOP + Clean Coding**: WordPress / HTTP / エディター I/O は外側の Adapter / Facade、検証・パーセント・閾値・スラッグ正規化は内側の純関数です。

* **Provider registries**: translation (`translate`) and similarity AI (`compare`) as data + functions, not class hierarchies

  **プロバイダ・レジストリ**: 翻訳 (`translate`) と類似度 AI (`compare`) をデータ + 関数で持ち、クラス階層は置きません。

* **REST facade**: authorize → parse title → `generate_candidate` → JSON

  **REST Facade**: 認可 → タイトル解析 → `generate_candidate` → JSON です。

* Gutenberg JSX uses `wp.element` (Vite externalizes `react` / `react/jsx-runtime` to avoid a second React)

  Gutenberg の JSX は `wp.element` を使います (Vite が `react` / `react/jsx-runtime` を外部化し、React の二重読み込みを避けます)。

Design details: [docs/specs.md](./docs/specs.md)

設計の詳細は [docs/specs.md](./docs/specs.md) です。

## API endpoints

* `POST /wp-json/s2j-slug-generater/v1/generate`
  * Body: `{ "title": "..." }` (no `nonce` in the body)
  * Auth: `X-WP-Nonce` (`wp_rest`) and `edit_posts`
  * Success `HTTP 200`: `candidates` (one English string) plus `translated_title` / `similarity` (percent) / `accepted`
  * Domain errors `HTTP 400`: `error.code` / `error.message` such as `missing_translation_api_key` / `missing_similarity_ai_key`

  * ボディ: `{ "title": "..." }` (ボディに `nonce` は置きません)
  * 認可: `X-WP-Nonce` (`wp_rest`) と `edit_posts`
  * 成功 `HTTP 200`: `candidates` (英訳1件) に加え `translated_title` / `similarity` (パーセント) / `accepted`
  * ドメインエラー `HTTP 400`: `error.code` / `error.message` (`missing_translation_api_key` / `missing_similarity_ai_key` など)

## Internationalization

Text domain: `s2j-slug-generater`. Locale files use `{domain}-{locale}` such as `languages/s2j-slug-generater-ja.po` and `.mo`.

テキストドメインは `s2j-slug-generater` です。ロケールファイル名は `{domain}-{locale}` です。例: `languages/s2j-slug-generater-ja.po` と `.mo`。

Gutenberg also needs Jed JSON via `wp_set_script_translations`. After editing `.po`:

Gutenberg では `wp_set_script_translations` による Jed JSON も必要です。`.po` を編集したあとは次を実行します。

```bash
npm run makepot
npm run makejson
```

## License

GPL-3.0-or-later

ライセンスは GPL-3.0-or-later です。

## Support

Issues and requests: [github.com/stein2nd/s2j-slug-generater](https://github.com/stein2nd/s2j-slug-generater)

不具合報告と要望は [github.com/stein2nd/s2j-slug-generater](https://github.com/stein2nd/s2j-slug-generater) へお願いします。

## Changelog

See [CHANGELOG.md](./CHANGELOG.md)

変更履歴は [CHANGELOG.md](./CHANGELOG.md) を参照してください。
