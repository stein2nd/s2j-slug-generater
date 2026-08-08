# S2J Slug Generater - CHANGELOG

## unreleased

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
