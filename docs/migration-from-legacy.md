# S2J Slug Generater - 旧 SPEC からの移行対応表

← [索引](./specs.md)

旧仕様 (`docs/archive/SPEC.md`、`docs/archive/SPEC_mod.md`) と、本仕様の対応関係を示します。

## 文書の位置付け

* 機能要件の正: `docs/archive/SPEC_mod.md` の内容 + 本ディレクトリで確定した実装方針
* 設計表現の正: `docs/` (本アプローチ。旧 `docs_mod/` から昇格)

| 文書 | 扱い |
| --- | --- |
| `docs/archive/SPEC.md` | 初期仕様 (レーベンシュタイン)。歴史的参照 |
| `docs/archive/SPEC_mod.md` | 機能要件のソース (S2J Similarity Service)。歴史的参照 |
| `docs/*` (本ディレクトリ。`archive/` 除く) | **採用仕様**。設計・実装は **FOP + Clean Coding**。CA は原則のみ借用。実装 (v2.0.5) と同期 |

## セクション対応

| 旧 (`SPEC`、`SPEC_mod`) | 新 |
| --- | --- |
| §1. はじめに | [specs.md](./specs.md) (基本アプローチ含む) |
| §2. 概要 | [01-overview.md](./01-overview.md) |
| §3. 構成 | [10-build-and-i18n.md](./10-build-and-i18n.md) |
| §4. ビルド / 依存 | [10-build-and-i18n.md](./10-build-and-i18n.md) |
| §5. 国際化 | [10-build-and-i18n.md](./10-build-and-i18n.md) |
| §6.1. Template Method / Abstract Factory | [02-fp-principles.md](./02-fp-principles.md), [05-providers.md](./05-providers.md) |
| §6.1.x. DeepL / Google | [05-providers.md](./05-providers.md) |
| §6.2. 管理画面 | [07-admin.md](./07-admin.md) |
| §6.3. / §6.4. Gutenberg / Classic | [08-editor.md](./08-editor.md) |
| §6.5. 生成ロジック | [04-pipeline.md](./04-pipeline.md), [06-similarity.md](./06-similarity.md) |
| §6.6. 実装修正ポイント | 各文書 + [10-build-and-i18n.md](./10-build-and-i18n.md) |
| §7. REST | [09-rest.md](./09-rest.md) |
| §8. 今後の拡張 | [10-build-and-i18n.md](./10-build-and-i18n.md) |

## 設計概念の置き換え (FOP 版)

| 旧 | 新 (FOP + Clean Coding) |
| --- | --- |
| Template Method | 薄いオーケストレータ + 純ステップ + 注入 Adapter |
| Abstract Factory | `TranslationProvider` / `SimilarityAiProvider` レジストリ (データ + 関数) |
| Strategy (類似度) | 記述子の `compare` / 薄い Adapter |
| 手続き的クリック手順 | パイプライン表 + UI イベント対応 (Command は関数で可) |
| 都度 `get_option` | 不変 `PluginConfig` の受け渡し |
| 例外中心 | `Result` / `{ success, error }` (境界で畳む) |
| (なし) | Clean Coding を全体作法に。CA は依存の向きのみ借用 |

## `SPEC.md` と `SPEC_mod.md` / 実装の差分 (短縮)

| 項目 | SPEC | SPEC_mod / docs (実装 v2.0.5) |
| --- | --- | --- |
| 類似度 | レーベンシュタイン: 0〜100 | コサイン: 0.0〜1.0 (表示 %) |
| 設定 | 翻訳 API 中心 | + 類似度用 AI 選択 / キー / モデル / ロケール / DeepL API Plan。案内は各記述子から組立 |
| 閾値 UI | 0〜100、step: 10 | 0.0〜1.0、step: 0.1 (表示 %)。旧 percent は自動変換 |
| REST | `{ candidates }` | `{ success, data: { candidates, similarity, translated_title, accepted } }` |
| REST nonce | ボディ `nonce` 想定可 | `X-WP-Nonce` (`wp_rest`) のみ。ボディ `nonce` なし |
| Gutenberg | (不定) | `PluginDocumentSettingPanel` |
| Classic | MetaBox | `edit_form_after_title` |
| PHP 依存 | なし想定 | `s2j/similarity-service` (`^2.0`) |

## 意識的に明確化した点

1. 類似度比較は、**元タイトル vs 逆翻訳**
2. 編集中タイトルは、**クライアント取得 → REST**
3. **外枠 オブジェクト指向 / 内側 関数型プログラミング**。API キー、Option、DOM は Adapter に閉じる
4. スラッグ化は、**クライアント純関数 + 適用 Adapter**。`accepted: false` では無効
5. 設計の正は、関数型プログラミング単体ではなく **FOP + Clean Coding** (CA フルは採らない)
6. DeepL ホストは **plan 設定**で解決する (`:fx` だけで Free と断定しない)

## 変更履歴

* **docs v0.5.0**: 類似度 AI も翻訳と同じ記述子レジストリ (案内 URL・モデル・`compare`)。組込みは `openai`
* **docs v0.4.0**: 採用仕様を `docs_mod/` から `docs/` に昇格。旧 SPEC は `docs/archive/`
* **docs_mod v0.3.0**: 実装 v2.0.5に同期 (エディター配置、REST nonce、`accepted` UX、DeepL plan、Option キー、構成ツリー)
* **docs_mod v0.2.0**: 基本アプローチを **FOP + Clean Coding** に更新。CA は借用原則のみと明示し、各文書をリライト
* **docs_mod v0.1.0**: `SPEC.md` / `SPEC_mod.md` を FP 視点で細分化
