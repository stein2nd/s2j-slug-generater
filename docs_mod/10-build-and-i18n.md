# S2J Slug Generater - ビルド・国際化・構成

← [索引](./specs.md)

構成は、**FOP + Clean Coding** で追える配置を優先します。Clean Architecture の定型レイヤ名に無理に合わせません。

## プロジェクト構成 (論理)

```text
s2j-slug-generater/
├─ package.json
├─ composer.json              # s2j/similarity-service
├─ vite.config.ts
├─ tsconfig.json
├─ s2j-slug-generater.php
├─ uninstall.php
├─ includes/                  # 外枠: Facade / Adapter / 薄い Orchestrator (PHP)
├┬─ src/
│├─ admin/                  # 設定 UI (外枠)
│├─ gutenberg/              # 編集 UI + Editor Adapter
│├─ classic/
│├─ styles/
│└─ types/                  # ドメインに近い不変データの型
├─ dist/
├─ languages/
└─ docs_mod/
```

### PHP / TypeScript の担当 (読みやすさ優先)

* 神クラスに Domains と HTTP を溜めないこと。
* かといって、ファイルを儀式的に増やしすぎないこと。

| 役割 | 層 | 例 |
| --- | --- | --- |
| 純関数 | 内側 関数型プログラミング | `normalize_to_slug`、`to_percent`、`passes_threshold` |
| プロバイダ記述子 | 外枠データ + Adapter | `providers/deepl.php` 等 |
| 類似度 Adapter | 外枠 | `similarity/embedding.php` (実装例として特定 AI サービスへ接続) |
| オーケストレータ | 薄く | `generate_candidate(...)` |
| WP Facade | 外枠 | `RestController`、`SettingsPage`、enqueue |

## ビルド

npm 依存バージョンは、「S2J Alliance Manager」と統一 (旧 `SPEC_mod.md` §4.2. を踏襲)。

* Vite + TypeScript + SCSS、IIFE (`vite.config.ts`)
* Classic は、WP 同梱 jQuery 可 (`jQuery(function($) { ... })`)
* 出力: `./dist`

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
* **純関数に表示文言を埋め込まない** (メッセージ組立は Facade / UI)

## 拡張の足し方 (FOP)

| 拡張 | やり方 |
| --- | --- |
| 翻訳 API 追加 | レジストリに記述子 + `translate` を1件 |
| 類似度戦略の変更 | `compareSimilarity` / Adapter の差し替え |
| 候補ランキング | `GenerateSuccess` を純関数で enrich |
| wp-cli | 同じ `generateCandidate` を CLI Facade から呼ぶ |

次: [migration-from-legacy.md](./migration-from-legacy.md)
