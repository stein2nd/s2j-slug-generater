# S2J Slug Generater - CHANGELOG

## unreleased

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
