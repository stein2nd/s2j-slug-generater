# S2J Slug Generater - パイプライン

← [索引](./specs.md)

候補生成とスラッグ化を、**薄いオーケストレータ + 純ステップ + 注入 Adapter** で表します (FOP)。  
旧 SPEC の番号付きクリック手順は、ここでの合成順に対応します。

Clean Coding: オーケストレータは「順序と失敗の引き回し」だけにとどめ、HTTP 詳細や DOM 操作を書きません。

## 依存 (DI)

```text
Deps = {
  translate: (req: TranslateRequest) => Effect<Result<string, TranslateError>>
  compareSimilarity: (req: SimilarityRequest) => Effect<Result<SimilarityRatio, SimilarityError>>
}
```

* `PluginConfig` は、呼び出し側で `loadConfig` (外枠) して渡すのが基本
* `translate` は、プロバイダ・レジストリから取り出し、部分適用して固定してよい (Strategy を関数で代替)

## A. 候補生成 `generateCandidate`

### シグネチャ

```text
generateCandidate(
  title: Title,
  config: PluginConfig,
  deps: Deps
) -> Effect<GenerateResult>
```

### ステップ

| # | ステップ | 層 | 入 → 出 |
| --- | --- | --- | --- |
| 1 | タイトル検証 | 内側 pure | `Title?` → `Result<Title, EmptyTitle>` |
| 2 | 英訳 | 外枠 Adapter | `Title` → `TranslatedText` (`source → en`) |
| 3 | 逆翻訳 | 外枠 Adapter | `TranslatedText` → `ReversedText` (`en → sourceLanguage`) |
| 4 | 類似度比較 | 外枠 Adapter | `(Title, ReversedText)` → `SimilarityRatio` |
| 5 | パーセント化・整形 | 内側 pure | `ratio` → `percent` / 表示用の文字列 |
| 6 | 閾値判定 | 内側 pure | `(ratio, threshold)` → `accepted` |
| 7 | 結果組立 | 内側 pure | 上記 → `GenerateSuccess` (不変) |

擬似コード:

```text
generateCandidate(title, config, deps) =
  validateTitle(title)
    |> andThen(t =>
         deps.translate({ text: t, source: config.sourceLanguage, target: "en", apiKey: config.translationApiKey })
       )
    |> andThen(translated =>
         deps.translate({ text: translated, source: "en", target: config.sourceLanguage, apiKey: config.translationApiKey })
           |> map(reversed => { translated, reversed })
       )
    |> andThen(({ translated, reversed }) =>
         deps.compareSimilarity({
           baseText: title,
           targetText: reversed,
           apiKey: config.similarityAiApiKey,
           model: config.similarityAiModel,
           language: config.sourceLanguage,
           locale: config.locale
         })
           |> map(ratio => { translated, ratio })
       )
    |> map(({ translated, ratio }) => ({
         translatedTitle: translated,
         similarity: toPercent(ratio),
         candidates: [translated],
         accepted: passesThreshold(ratio, config.similarityThreshold)
       }))
```

### UI への写像

| 出力 | UI |
| --- | --- |
| `translatedTitle` | 「スラッグ候補」 |
| `formatPercentLabel(similarity)` | 「類似度」+ ` %` |
| `accepted === false` | 非採用扱い (類似度は表示)。方針は実装で一貫させる |

`ReversedText` は、UI に出しません。

### 比較対象 (採用)

元タイトルと逆翻訳を、比較対象します (ラウンドトリップの意味保持)。`SPEC_mod` に準拠します。

## B. スラッグ化 `slugifyAndApply`

```text
slugifyAndApply(
  candidate: SlugCandidate,
  applySlug: (slug: Slug) => Effect<Unit>   // Editor Adapter (DI)
) -> Effect<Result<Slug, EmptyCandidate>>
```

| # | ステップ | 層 | 内容 |
| --- | --- | --- | --- |
| 1 | 空チェック | 内側 pure | 空なら Err |
| 2 | `normalizeToSlug` | 内側 pure | 記号・スペースを正規化 |
| 3 | `applySlug` | 外枠 | Gutenberg store / Classic DOM |

`normalizeToSlug` の規則は、純関数として固定し、エディター間で共有します。

## C. 入口ごとの置き場所

API キーは、サーバー Option にとどめ、ブラウザにさらしません。

| 入口 | 外枠 | 中身 |
| --- | --- | --- |
| REST `POST .../generate` | Facade が認可・パース | `generateCandidate` |
| Gutenberg / Classic「候補生成」 | REST 呼び出し Adapter | 返却を UI 状態へ純写像 |
| 「スラッグ化」 | `applySlug` Adapter | `normalizeToSlug` |

次: [05-providers.md](./05-providers.md)
