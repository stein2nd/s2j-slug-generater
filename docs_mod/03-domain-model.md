# S2J Slug Generater - ドメインモデル (不変データ)

← [索引](./specs.md)

FOP の **内側** に置くデータの形を定義します。実装言語の型にそのまま写せなくてもよいが、**名前・不変条件・純関数の入出力** はそろえます。

Clean Coding: 型名は UI 文言や WP API 名ではなく、ドメインの意味で付けます。

## 基本値

```text
Title            = non-empty string          // 投稿タイトル
TranslatedText   = string                    // 英訳結果 (スラッグ候補の元)
ReversedText     = string                    // 逆翻訳結果 (類似度検証用・UI 非表示)
SlugCandidate    = string                    // 「スラッグ候補」欄の値
Slug             = string                    // 正規化済みスラッグ
LanguageCode     = string                    // 例: "ja" (プロバイダ仕様に準拠)
Locale           = string                    // 例: "ja_JP"
SimilarityRatio  = float in [0.0, 1.0]       // コサイン類似度
SimilarityPercent = float in [0.0, 100.0]    // 表示・REST 用
Threshold        = SimilarityRatio           // 設定値
ProviderId       = "deepl" | "google" | ...
```

これらは書き換えずに **新しい値を返す** 前提で扱います (不変)。

## 設定 (Config)

管理画面 (外枠) で永続化し、パイプラインに **不変レコード** として渡します。

```text
PluginConfig = {
  providerId: ProviderId
  translationApiKey: string
  sourceLanguage: LanguageCode          // default: "ja"
  similarityAiApiKey: string            // 類似度（埋め込み）用 AI サービスの API キー
  similarityAiModel: string             // 類似度（埋め込み）用 AI モデル。default: "text-embedding-3-small"
  locale: Locale                        // default: "ja_JP"
  similarityThreshold: Threshold        // default: 0.8
}
```

| 操作 | 層 | 説明 |
| --- | --- | --- |
| `loadConfig` | 外枠 Adapter | `get_option` の合成 → `PluginConfig` |
| `saveConfig` | 外枠 Adapter | `PluginConfig` → `update_option` |
| ビジネスロジック関数 | 内側 関数型プログラミング | `PluginConfig` を引数で受け取る。Option API を呼ばない |

## 翻訳プロバイダ記述子

外枠のレジストリが持つデータ + 接続関数 (Abstract Factory の代替) です。

```text
TranslationProvider = {
  id: ProviderId
  planUrl: Url
  freeKeyUrl: Url
  endpoint: Url
  languages: LanguageCode[]
  translate: (req: TranslateRequest) => Effect<Result<TranslatedText, TranslateError>>
}

TranslateRequest = {
  text: string
  source: LanguageCode
  target: LanguageCode
  apiKey: string
}
```

具体値は [05-providers.md](./05-providers.md) を参照してください。

## 類似度

```text
SimilarityRequest = {
  baseText: Title
  targetText: ReversedText
  apiKey: string
  model: string
  language: LanguageCode
  locale: Locale
}

SimilarityScore = {
  ratio: SimilarityRatio
  percent: SimilarityPercent
}

// 内側の純関数
toPercent(ratio: SimilarityRatio): SimilarityPercent
formatPercentLabel(percent: SimilarityPercent): string   // "n.99" (小数第2位)
passesThreshold(ratio: SimilarityRatio, threshold: Threshold): bool
```

詳細は [06-similarity.md](./06-similarity.md) を参照してください。

## パイプライン結果

```text
GenerateSuccess = {
  translatedTitle: TranslatedText
  similarity: SimilarityPercent
  candidates: SlugCandidate[]
  accepted: bool                        // passesThreshold の結果
}

GenerateFailure = {
  code: string
  message: string                       // 表示文言は境界で組み立ててもよい
}

GenerateResult = Ok(GenerateSuccess) | Err(GenerateFailure)
```

## スラッグ化

```text
// 内側 pure
normalizeToSlug(text: SlugCandidate): Slug

// 外枠 effect (Editor Adapter)
applySlug(slug: Slug): Effect<Unit>
```

次: [04-pipeline.md](./04-pipeline.md)
