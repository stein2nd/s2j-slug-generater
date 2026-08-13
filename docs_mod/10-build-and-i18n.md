# S2J Slug Generater - ビルド・国際化・構成

← [索引](./specs.md)

構成は、**FOP + Clean Coding** で追える配置を優先します。Clean Architecture の定型レイヤ名に無理に合わせません。

## プロジェクト構成 (論理)

```text
s2j-slug-generater/
├─ package.json
├─ composer.json          # s2j/similarity-service
├─ vite.config.ts
├─ tsconfig.json
├─ s2j-slug-generater.php   # bootstrap (vendor autoload + modules)
├─ uninstall.php
├┬─ includes/
│├─ RestController.php      # REST Facade
│├─ Admin/SettingsPage.php  # 設定 Facade (Settings API)
│├─ Config/PluginConfig.php # Option Adapter
│├─ Domain/pure.php         # 内側純関数
│├─ Editor/                 # GutenbergMount / ClassicMount
│├─ Pipeline/generate_candidate.php
│├─ Providers/              # 翻訳レジストリ + deepl / google
│└─ Similarity/             # 類似度 AI レジストリ + openai / compare Adapter
├┬─ src/
│├─ vite-env.d.ts
│├─ admin/                  # 設定画面用アセット (スタイル中心)
│├─ classic/                # Title 直下パネル UI
│├─ domain/panel.ts         # エディター共有の純写像 / normalizeToSlug
│├─ gutenberg/              # PluginDocumentSettingPanel UI
│├─ styles/
│└─ types/                  # WP モジュール shim 等
├─ dist/                      # ビルド成果 (Git 管理外)
├─ vendor/                    # Composer (Git 管理外。利用時は composer install)
├─ patches/
├─ scripts/
├─ languages/
├─ docs/                      # 採用仕様 (archive/ に旧 SPEC)
└─ docs_mod/                  # 仕様追加の検討用ドラフト
```

### PHP / TypeScript の担当 (読みやすさ優先)

* 神クラスに Domains と HTTP を溜めないこと。
* かといって、ファイルを儀式的に増やしすぎないこと。

| 役割 | 層 | 例 |
| --- | --- | --- |
| 純関数 | 内側 関数型プログラミング | `includes/Domain/pure.php`、`src/domain/panel.ts` |
| プロバイダ記述子 | 外枠データ + Adapter | `includes/Providers/` (翻訳)、`includes/Similarity/` (類似度 AI) |
| 類似度 Adapter | 外枠 | 記述子の `compare` (`includes/Similarity/`) |
| オーケストレータ | 薄く | `includes/Pipeline/generate_candidate.php` |
| WP Facade | 外枠 | `RestController`、`SettingsPage`、Editor Mount |

## ビルド

npm 依存バージョンは、「[S2J Alliance Manager](https://github.com/stein2nd/s2j-alliance-manager)」と統一します。

* 旧仕様 (`docs/archive/SPEC_mod.md` §4.2) を踏襲。

* Vite + TypeScript + SCSS、IIFE (`vite.config.ts`)
* Gutenberg 向けに `react` / `react/jsx-runtime` は外部化し、`wp.element` 経由で JSX する (二重 React 防止)
* Classic は、WP 同梱 jQuery 可 (`jQuery(function($) { ... })`)
* 出力: `./dist` (Git 管理外)

| script | 内容 |
| --- | --- |
| `npm run build:dev` | minify 無効 |
| `npm run build:production` | minify 有効 |

```json
{
  "require": {
    "php": ">=8.0",
    "s2j/similarity-service": "^2.0"
  }
}
```

## 国際化

* `__()` / `_e()`、JS は `@wordpress/i18n`
* `languages/`、`.pot` は `makepot`、Text Domain `s2j-slug-generater`
* WordPress のロケールファイル名は `{domain}-{locale}` (ハイフン)。Poedit 既定の `{domain}.{locale}` では読み込まれない
  * 例: `s2j-slug-generater-ja.po` / `.mo` (日本語サイトの locale は `ja`)
* Gutenberg は `wp_set_script_translations` + Jed JSON が必要。`.mo` だけでは編集画面は変わらない
  * `npm run makejson` で `s2j-slug-generater-ja-s2j-slug-generater-gutenberg.json` を生成する
  * Poedit で `.po` を保存したあとも、JSON を作り直す
* **純関数に表示文言を埋め込まない** のが理想 (メッセージ組立は Facade / UI)。Adapter 境界でのエラー文言は許容する。

## 拡張の足し方 (FOP)

| 拡張 | やり方 |
| --- | --- |
| 翻訳 API 追加 | レジストリに記述子 + `translate` を1件 |
| 類似度 AI 追加 | レジストリに記述子 + `compare` を1件。案内 URL とモデル一覧は記述子から |
| 類似度戦略の変更 | 選択中記述子の `compare` / Adapter の差し替え |
| 候補ランキング | `GenerateSuccess` を純関数で enrich |
| wp-cli | 同じ `generateCandidate` を CLI Facade から呼ぶ |
