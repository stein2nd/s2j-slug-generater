# S2J Slug Generater - CHANGELOG

## unreleased

## 2.0.22 - 2026-10-24

### Added

* 翻訳ビルド用 npm スクリプト `makemo` / `makei18n`、Gutenberg 本番ビルド後の `makejson` (`postbuild:production:gutenberg`)

### Changed

* 対応 PHP の下限を v8.3に引き上げ (WordPress.org 推奨要件に合わせ、プラグインヘッダー / Composer / CI / `README.md`・`readme.md` / ドキュメントを統一)
* npm 依存モジュールの更新: `@wordpress/*` v8〜 v19系、`@wordpress/env` v12、Vite v8.3.5、`@s2j/docs-linter` v1.0.30等
* `makejson` を `wp i18n make-json languages --no-purge` に整理 (Gutenberg 用 JED はビルド後に生成)

## 2.0.21 - 2026-10-16

### Added

* ローカル PHPUnit (wp-env / Docker なし): `tests/smoke-local.sh`、`bin/install-wp-tests.sh`、`bin/phpunit-ci.sh`、`.env.tests.example`
* ユニット / 統合の PHPUnit 設定 (`phpunit.unit.xml.dist`、`tests/phpunit/Unit/`、`tests/phpunit/Integration/`)
* `docs/phpunit_local.md` (5件: ユニット4 + REST 統合1)
* Composer スクリプト `test:unit` / `test:integration` / `test:all`
* npm スクリプト `test:unit:php:unit-only`、`test:unit:php:local`、`test:unit:php` (CI)

### Changed

* GitHub Actions「PHPUnit」を MySQL サービス + Subversion + `install-wp-tests.sh` に変更 (wp-env 依存を廃止)
* `package.json` から `phpunit:wp-env` を削除

### Fixed

* `composer.json` の dev 依存バージョンを `composer.lock` / WPCS v3.x と整合 (`^9.6` / PHPCS `^3.10` 等)
* 統合テスト用 bootstrap / テストクラス向け IDE スタブ (`tests/stubs/wordpress-test-functions.php`、`wordpress-test-stubs.php`) で `tests_add_filter` / `WP_UnitTestCase` の未定義警告を解消

## 2.0.20 - 2026-10-16

### Fixed

* CHANGELOG の textlint 指摘に合わせ表記を修正 (`v2.0.18` と後続語の間のスペース)

## 2.0.19 - 2026-10-16

### Fixed

* `scripts/sync-version.mjs` が WPCS 形式の `define( 'S2J_SLUG_GENERATER_VERSION', … )` を検出・更新できるよう修正 (GitHub Actions「Version sync」失敗の解消)
* v2.0.18配布時にずれていた `S2J_SLUG_GENERATER_VERSION` を `package.json` と一致

## 2.0.18 - 2026-10-16

### Added

* PHP 静的解析・コーディング規約チェック用設定 (`phpstan.neon.dist`、`phpstan-bootstrap.php`、`phpcs.xml.dist`、`phpcs.wpcs.xml.dist`)
* Composer 開発依存とスクリプト (`phpstan`、`phpcs`、`phpcs:wpcs`、`phpcbf`、`lint:php`)。`package.json` から `npm run lint:php` 等で実行可能

### Changed

* `includes/` およびプラグインルート PHP を WordPress Coding Standards に合わせて整形 (Yoda 条件、ファイル先頭コメント、設定画面のエスケープ等)
* PSR-12チェック (`phpcs.xml.dist`) は WPCS と矛盾するルールを除外し、タブインデントの WordPress スタイルと両立
* `S2J_Slug_Generater_Plugin_Config` の定数に `public` 可視性を付与

### Fixed

* PHPStan がプラグイン定数 (`S2J_SLUG_GENERATER_*`) を解決できるよう bootstrap を追加
* DeepL プロバイダで非推奨の `curl_close()` を `unset( $ch )` に置き換え

## 2.0.17 - 2026-10-15

### Added

* wp-env 上の PHPUnit 実行 (`phpunit:wp-env`、`@wordpress/env`、`.wp-env.json`、`phpunit.xml.dist`、`tests/`)

### Fixed

* GitHub Actions「PHPUnit」向けに `package.json` の `phpunit:wp-env` スクリプトを追加
* CI (PHP v8.2) で `composer install` が通るよう `config.platform.php` を設定し、`doctrine/instantiator` を2.0.0に固定

## 2.0.16 - 2026-10-15

### Added

* GitHub Actions ワークフローを追加 (`dist-zip.yml`、tag push / 手動で配布 zip 生成、`phpunit.yml`、wp-env 上の PHPUnit)
* 配布 zip 組み立てスクリプト `tools/dist/make-dist-zip.mjs` を追加
* `package.json` に配布用 npm スクリプト `dist:build` / `dist:zip` / `dist` を追加

### Changed

* npm 依存モジュールの更新 (`ncu` / `ncu -u`)。`@wordpress/components` v42、`@wordpress/element` v9、`@s2j/docs-linter` v1.0.29、React v19.3.1、Vite v8.3.4等
* Composer 依存 `s2j/similarity-service` を v2.0.12から v2.0.13に更新 (`composer.lock`)
* `.gitignore` の build 出力パスを整理 (`tools/dist/` を誤 ignore しないよう `/dist/` 等に)
* textlint の許可パターンに `#status-*` アンカー ID を追加
* 配布 zip に `vendor/` と `readme.md` を同梱するよう `make-dist-zip.mjs` を拡張
* `dist-zip.yml` に PHP / Composer setup を追加

## 2.0.15 - 2026-10-10

### Changed

* Composer 依存 `s2j/similarity-service` を v2.0.9から v2.0.12に更新 (`composer.lock`)
* 現行ドキュメント (`README.md`、`docs/02-fp-principles.md`、`docs/04-pipeline.md`、`docs/05-providers.md`、`docs/08-editor.md`) の助詞表現を統一

## 2.0.14 - 2026-10-08

### Changed

* 現行ドキュメント (`README.md`、`docs/specs.md`、`docs/02-fp-principles.md`) の「次」を「下記」に、「とき」を「場合」に統一

## 2.0.13 - 2026-10-05

### Changed

* アーカイブ仕様 (`docs/archive/SPEC.md`、`SPEC_mod.md`) の「以下」を「下記」に統一

## 2.0.12 - 2026-10-04

### Changed

* npm 依存モジュールの更新 (`eslint` v10.12、`@s2j/docs-linter` v1.0.27等)
* Composer 依存モジュールの更新 (`s2j/similarity-service` v2.0.9)

### Fixed

* 管理画面の類似度スライダーを ID セレクタから kebab-case クラスに変更し、`selector-id-pattern` 違反を解消

## 2.0.11 - 2026-10-03

### Changed

* npm 依存モジュールの更新 (`ncu` / `ncu -u`)。`stylelint` v17.16、Vite v8.3.2、`@s2j/docs-linter` v1.0.26等
* Composer 依存 `s2j/similarity-service` を v2.0.7から v2.0.8に更新 (`composer.lock`)

## 2.0.10 - 2026-09-26

### Changed

* npm 依存モジュールの更新 (`ncu` / `ncu -u`)。`@wordpress/components` v41、`@wordpress/block-editor` v18、Vite v8.3.1等
* Composer 依存 `s2j/similarity-service` を v2.0.6から v2.0.7に更新 (`composer.lock`)
* `README.md` の Vite バージョンバッジを v8.3に更新

## 2.0.9 - 2026-09-09

### Changed

* npm 依存モジュールの更新 (`ncu` / `ncu -u`)。`eslint` v10.10、`stylelint` v17.15、`@s2j/docs-linter` v1.0.24等
* Composer 依存 `s2j/similarity-service` を v2.0.4から v2.0.6に更新 (`composer.lock`)
* `README.md` を現行実装に同期 (コサイン類似度、Gutenberg 文書設定パネル、Classic タイトル直下、候補1件、類似度 AI、FOP)
* `README.md` の英語本文の直後に日本語対訳を併記

## 2.0.8 - 2026-08-31

### Changed

* npm 依存モジュールの更新 (`ncu` / `ncu -u`)。`@wordpress/components` v40、`@wordpress/block-editor` v17、Vite v8.2.2等

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
    * `accepted: false` の場合スラッグ化ボタンを無効化
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
