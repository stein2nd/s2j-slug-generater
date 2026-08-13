# S2J Slug Generater - 投稿の編集画面

← [索引](./specs.md)

Gutenberg / Classic は、**同じ内側操作を、異なる Editor Adapter で再現** します (FOP)。

* Clean Coding: エディター差分は Adapter に閉じ込め、候補生成の意味 (REST 契約・表示フィールド) は共有 (`src/domain/panel.ts`)。

## 共通 UI

| 要素 | 役割 |
| --- | --- |
| 「候補生成」 | REST → 結果を UI 状態へ |
| 「スラッグ候補」 | `translated_title` (編集可) |
| 「類似度」+ ` %` | `formatPercentLabel(similarity)` |
| 「スラッグ化」 | `accepted` 時のみ。`normalizeToSlug` (pure) → `applySlug` (Adapter) |

## UI 状態 (不変更新)

* `accepted === false`: 類似度は表示し、スラッグ化ボタンを無効化する
* Gutenberg では、Current Slug の参照表示を置いてよい

```text
EditorPanelState = {
  candidate: SlugCandidate
  similarityLabel: string          // 空 or "n.99"
  isGenerating: bool
  errorMessage: string | null
  accepted: bool                   // REST data.accepted
}

// REST 成功 data (snake_case) を写像する
onGenerateSuccess(state, success) = {
  ...state,
  candidate: success.translated_title || success.candidates[0] || "",
  similarityLabel: formatPercentLabel(success.similarity),
  isGenerating: false,
  errorMessage: null,
  accepted: success.accepted
}
```

## タイトル取得 Adapter

編集中タイトルとずれうる `get_the_title()` 依存は、編集フローでは採用しません。

| 環境 | 取得 |
| --- | --- |
| Gutenberg | `getEditedPostAttribute('title')` のみ (DOM / iframe フォールバックは採らない) |
| Classic | `#title` |
| REST 内 | リクエストの `title` (クライアント送信を正とする) |

## スラッグ適用 Adapter

| 環境 | 適用 |
| --- | --- |
| Gutenberg | `editPost({ slug })` |
| Classic | `#post_name` + `#editable-post-name` (+ `#editable-post-name-full` があれば同期) |

## Gutenberg / Classic

| | Gutenberg | Classic |
| --- | --- | --- |
| マウント | `PluginDocumentSettingPanel` (文書設定サイドバー) | `edit_form_after_title` (Title 直下パネル。MetaBox ではない) |
| 生成 | `apiFetch` → REST (`X-WP-Nonce` は WP 標準 middleware) | localize の `restUrl` / `nonce` (`wp_rest`) → `fetch` + `X-WP-Nonce` |
| スラッグ化 | pure + `editPost` (`accepted` 時のみ) | pure + DOM Adapter (`accepted` 時のみ) |

## イベント対応

Command はクラスにせず、ハンドラ関数で十分です (振る舞いパターンの関数化)。

| 操作 | コマンド | 内側 | 外枠 |
| --- | --- | --- | --- |
| 候補生成 | `GenerateCandidate` | レスポンス → state 写像 | REST |
| 候補手編集 | `EditCandidate` | state.candidate 更新 | — |
| スラッグ化 | `ApplySlug` | `normalizeToSlug` (`accepted` 必須) | `applySlug` |
