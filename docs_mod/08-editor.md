# S2J Slug Generater - 投稿の編集画面

← [索引](./specs.md)

Gutenberg / Classic は、**同じ内側操作を、異なる Editor Adapter で再現** します (FOP)。
UI 配置は、どちらも Post Title 直下とします。

Clean Coding: エディター差分は Adapter に閉じ込め、候補生成の意味 (REST 契約・表示フィールド) は共有します。

## 共通 UI

| 要素 | 役割 |
| --- | --- |
| 「候補生成」 | REST → 結果を UI 状態へ |
| 「スラッグ候補」 | `translatedTitle` (編集可) |
| 「類似度」+ ` %` | `formatPercentLabel(similarity)` |
| 「スラッグ化」 | `normalizeToSlug` (pure) → `applySlug` (Adapter) |

## UI 状態 (不変更新)

```text
EditorPanelState = {
  candidate: SlugCandidate
  similarityLabel: string          // 空 or "n.99"
  isGenerating: bool
  errorMessage: string | null
}

onGenerateSuccess(state, success) = {   // 純写像
  ...state,
  candidate: success.translatedTitle,
  similarityLabel: formatPercentLabel(success.similarity),
  isGenerating: false,
  errorMessage: null
}
```

## タイトル取得 Adapter

編集中タイトルとずれうる `get_the_title()` 依存は、編集フローでは採用しません。

| 環境 | 取得 |
| --- | --- |
| Gutenberg | `getEditedPostAttribute('title')` |
| Classic | `#title` |
| REST 内 | リクエストの `title` (クライアント送信を正とする) |

* 参考実装パターン: `bousaid-ai-slug-generator` のタイトル参照・スラッグ適用。配置は本 SPEC (Title 直下) を優先。

## スラッグ適用 Adapter

| 環境 | 適用 |
| --- | --- |
| Gutenberg | `editPost({ slug })` |
| Classic | `#post_name` + `#editable-post-name` の同期 |

* 参考実装パターン: `bousaid-ai-slug-generator` のタイトル参照・スラッグ適用。配置は本 SPEC (Title 直下) を優先。

## Gutenberg / Classic

| | Gutenberg | Classic |
| --- | --- | --- |
| マウント | Title 直下 Slot | Title 直下 MetaBox |
| 生成 | `apiFetch` → REST | localize の `restUrl` / `nonce` → REST |
| スラッグ化 | pure + `editPost` | pure + DOM Adapter |

## イベント対応

Command はクラスにせず、ハンドラ関数で十分です (振る舞いパターンの関数化)。

| 操作 | コマンド | 内側 | 外枠 |
| --- | --- | --- | --- |
| 候補生成 | `GenerateCandidate` | レスポンス → state 写像 | REST |
| 候補手編集 | `EditCandidate` | state.candidate 更新 | — |
| スラッグ化 | `ApplySlug` | `normalizeToSlug` | `applySlug` |

次: [09-rest.md](./09-rest.md)
