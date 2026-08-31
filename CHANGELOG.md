# S2J Slug Generater - CHANGELOG

## unreleased

## 2.0.8 - 2026-08-31

### Changed

* npm 依存モジュールの更新 (`ncu` / `ncu -u`)。`@wordpress/components` v40、`@wordpress/block-editor` v17、Vite v8.2.2 等

## 2.0.7 - 2026-08-13

### Changed

* `docs_mod/` を Git 追跡対象に戻し、今後の仕様検討用ドラフト置き場とする

## 2.0.6 - 2026-08-13

### Added

* 類似度 AI プロバイダ・レジストリ (`openai`) を追加。管理画面の案内リンクとモデル一覧を記述子から組み立てる

### Changed

* 採用仕様を実装 v2.0.5に同期し、`docs_mod/` から `docs/` に昇格。旧仕様 (`SPEC.md`、`SPEC_mod.md`) は `docs/archive/` に移動
* プラグインヘッダーを整備 (Author / Author URI、Requires at least v6.3、Tested up to v6.8、Requires PHP v8.2、等)
* 類似度設定を翻訳プロバイダと同じ記述子レジストリにそろえる仕様を `docs/` に追加 (案内 URL、モデル一覧、`compare`。組込みは `openai`)
* 類似度キー案内の OpenAI URL 直書きをやめ、翻訳プロバイダと同じ `formatSimilarityApiKeyHelp` にそろえた

### Fixed

* 翻訳ファイル名を WordPress 規約 (`s2j-slug-generater-ja.mo`) にそろえ、Gutenberg に `wp_set_script_translations` と handle 名の Jed JSON を追加

## 2.0.5 - 2026-08-12

### Added

* Composer 依存として [`s2j/similarity-service`](https://packagist.org/packages/s2j/similarity-service) (`^2.0`) を導入 (`composer.json` / `composer.lock`)
* プラグイン本体で `vendor/autoload.php` を読み込む処理を追加
* 仕様再設計ドキュメント群 `docs_mod/` を追加 (FOP + Clean Coding 土台、CA は借用原則のみ)
* 実装追加
    * FOP 構成のサーバー実装: Domain 純関数、PluginConfig Adapter、翻訳プロバイダ・レジストリ、Similarity Adapter、`generate_candidate` オーケストレータ
    * 管理画面に類似度用 AI API キー / モデル / ロケール設定を追加
    * REST 成功レスポンスに `accepted` を追加
    * Gutenberg を `PluginDocumentSettingPanel` に配置、Classic を Title 直下 (`edit_form_after_title`) に配置

### Changed

* 設計方針を「FOP + Clean Coding」に整理し、類似度用の設定名を `similarityAiApiKey` / `similarityAiModel` など汎用名称へ統一 (`docs_mod`)
* `.gitignore` に `vendor/` を追加 (Git 管理外。リリース成果物には同梱する方針)
* 実装変更
    * 類似度をレーベンシュタインから `s2j/similarity-service` のコサイン類似度へ切替
    * REST 認可を `X-WP-Nonce` (`wp_rest`) に一本化 (ボディ `nonce` 廃止)
    * 候補を英訳テキスト1件 (`candidates: [translated]`) に簡素化。スラッグ化はクライアント `normalizeToSlug`
    * 類似度閾値を ratio (0.0–1.0) で保持。既存 percent 値は起動時に自動変換
    * `accepted: false` のときスラッグ化ボタンを無効化
    * 生成ロジックとエディター・マウントを分割

### Fixed

* Gutenberg パネル描画クラッシュを修正 (Vite が React の `jsx-runtime` を同梱し `wp.element` と要素型が衝突していた)
* DeepL 連携の安定化
    * JSON + `Authorization` + cURL 直たたきに変更
    * API Plan 設定 (auto / free / pro)。auto は `api.deepl.com` 優先、失敗時に api-free にリトライ
    * `:fx` 接尾辞だけでの Free 判定を廃止 (Individual 等で `:fx` でも Pro ホストのキーがあるため)
    * Translate 権限なしキー向けに `/v2/usage` 診断メッセージを追加
    * API キー入力を text 欄に変更 (パスワードマネージャーによる誤送信対策)

## 2.0.4 - 2026-08-11

### Changed

* npm 依存モジュールの更新 (`ncu` / `ncu -u`)
* TypeScript v7.0 (`tsc`) と `@typescript/typescript6` (`typescript-eslint` 向け) の side-by-side 構成を導入
* TypeScript v7対応のため `tsconfig.json` から `baseUrl` を削除し、`paths` を相対パス化
* SCSS モジュール用に `src/vite-env.d.ts` を追加
* Gutenberg の `@wordpress/core-data` インポートを `store as coreStore` に変更
* 本体に型定義があるため `@types/wordpress__blocks` / `@types/wordpress__wordcount` を削除
* ESLint v10と `eslint-plugin-react` の互換のため、React バージョンを設定で明示
* Vite 設定の `__dirname` を `import.meta.dirname` に置き換え、無効な `inlineDynamicImports` を削除

## 2.0.3 - 2026-08-08

### Changed

* npm 依存モジュールの更新 (`@wordpress/components` v38を含む WordPress パッケージ、Vite v8.2、`@s2j/docs-linter` 等)
* npm v12の install scripts 制限に対応するため、`package.json` に `@s2j/docs-linter` の `allowScripts` を追加
* `README.md` の Vite バージョンバッジを v8.2に更新

## 2.0.2 - 2026-07-23

### Fixed

* npm v12以降で `@s2j/docs-linter` の推移 Git 依存 (`textlint-rule-preset-wp-docs-ja`) により `npm install` が `EALLOWGIT` で失敗する問題を、`.npmrc` に `allow-git=all` を設定して修正

### Changed

* npm 依存モジュールの更新 (React、WordPress パッケージ、ESLint、TypeScript v7、Vite v8.1、`@s2j/docs-linter` 等)
* `README.md` に Vite バージョンバッジを追加

## 2.0.1 - 2026-06-11

### Fixed

* GitHub Actions の `npm ci` が React v19と `@wordpress/*` の peer dependency 競合で失敗する問題を、`.npmrc` に `legacy-peer-deps=true` を設定して修正

### Changed

* ドキュメント lint ワークフローのトリガー paths に `.npmrc` を追加

## 2.0.0 - 2026-06-11

### Breaking Changes

* ライセンスを GPL-2から GPL-3に変更

### Changed

* Gutenberg スクリプトの読み込み改善、Vite による WordPress モジュールの external 化
* Gutenberg エディターで `post_title` からスラッグ生成元の値を取得
* S2J Docs Linter を Git submodule から npm パッケージ (`@s2j/docs-linter`) に切り替え
* npm 依存モジュールの更新 (WordPress パッケージ、ESLint、TypeScript、Vite 等)
* ドキュメント lint 用 GitHub Actions ワークフローを追加
* ドキュメント更新 (`SPEC.md`、`SPEC_mod.md`、`README.md`)
