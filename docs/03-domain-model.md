# S2J Slug Generater - ドメインモデル (不変データ)

← [索引](./specs.md)

FOP の **内側** に置くデータの形を定義します。実装言語の型にそのまま写せなくてもよいが、**名前・不変条件・純関数の入出力** はそろえます。

* Clean Coding: 型名は UI 文言や WP API 名ではなく、ドメインの意味で付ける。

## 基本値

これらは書き換えずに **新しい値を返す** 前提で扱います (不変)。

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
SimilarityAiProviderId = "openai" | ...      // 埋め込み（類似度）用 AI
DeeplApiPlan     = "auto" | "free" | "pro"   // DeepL ホスト解決用
```

## 設定 (Config)

管理画面 (外枠) で永続化し、パイプラインに **不変レコード** として渡します。

```text
PluginConfig = {
  providerId: ProviderId
  translationApiKey: string
  sourceLanguage: LanguageCode          // default: "ja"
  similarityAiProviderId: SimilarityAiProviderId  // default: "openai"
  similarityAiApiKey: string            // 類似度（埋め込み）用 AI サービスの API キー
  similarityAiModel: string             // 類似度（埋め込み）用 AI モデル。選択中プロバイダの default に従う
  locale: Locale                        // default: "ja_JP" (設定保持。現行 Similarity 呼び出しには未使用)
  similarityThreshold: Threshold        // default: 0.8
  deeplApiPlan: DeeplApiPlan            // default: "auto" (DeepL 以外では無視)
}
```

| 操作 | 層 | 説明 |
| --- | --- | --- |
| `loadConfig` | 外枠 Adapter | `get_option` の合成 → `PluginConfig` |
| `saveConfig` | 外枠 Adapter | `PluginConfig` → `update_option` |
| ビジネスロジック関数 | 内側 関数型プログラミング | `PluginConfig` を引数で受け取る。Option API を呼ばない |

### Option キー (実装)

閾値マイグレーション: 既存 Option があり値が `> 1.0` なら percent とみなし `/100` して ratio に変換 (一度きり)。未登録なら default `0.8`。

| フィールド | Option キー |
| --- | --- |
| `providerId` | `s2j_slug_generater_translation_service` |
| `translationApiKey` | `s2j_slug_generater_api_key` |
| `sourceLanguage` | `s2j_slug_generater_source_language` |
| `similarityThreshold` | `s2j_slug_generater_similarity_threshold` |
| `similarityAiProviderId` | `s2j_slug_generater_similarity_ai_service` |
| `similarityAiApiKey` | `s2j_slug_generater_similarity_ai_api_key` |
| `similarityAiModel` | `s2j_slug_generater_similarity_ai_model` |
| `locale` | `s2j_slug_generater_locale` |
| `deeplApiPlan` | `s2j_slug_generater_deepl_api_plan` |
| (移行フラグ) | `s2j_slug_generater_threshold_migrated_v1` |

## 翻訳プロバイダ記述子

外枠のレジストリが持つデータ + 接続関数 (Abstract Factory の代替) です。

```text
TranslationProvider = {
  id: ProviderId
  planUrl: Url
  freeKeyUrl: Url
  endpoint: Url                         // 記述子の既定ホスト (DeepL は runtime で plan により解決)
  languages: LanguageCode[]
  translate: (req: TranslateRequest) => Effect<Result<TranslatedText, TranslateError>>
}

TranslateRequest = {
  text: string
  source: LanguageCode
  target: LanguageCode
  apiKey: string
  deeplApiPlan?: DeeplApiPlan           // DeepL のみ使用。Google は無視
}
```

具体値は [05-providers.md](./05-providers.md) を参照してください。

## 類似度 AI プロバイダ記述子

翻訳プロバイダと同じ持ち方です。外枠のレジストリが持つデータ + 接続関数。案内 URL とモデル一覧は、記述子に置き、管理画面は組み立てるだけにします。

```text
SimilarityAiProvider = {
  id: SimilarityAiProviderId
  signupUrl: Url                        // アカウント未所持者向けの登録入口
  billingUrl: Url                       // クレジット / 課金
  keysUrl: Url                          // API キー発行
  models: string[]                      // 先頭を default とする
  compare: (req: SimilarityRequest) => Effect<Result<SimilarityRatio, SimilarityError>>
}

SimilarityRequest = {
  baseText: Title
  targetText: ReversedText
  apiKey: string
  model: string
  language: LanguageCode                // 設定との対称性のため保持。現行 Adapter は未使用
  locale: Locale                        // 同上
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

詳細は、[06-similarity.md](./06-similarity.md) を参照してください。

## パイプライン結果

```text
GenerateSuccess = {
  translatedTitle: TranslatedText
  similarity: SimilarityPercent
  candidates: SlugCandidate[]
  accepted: bool                        // passesThreshold の結果
}

GenerateFailure = {
  code: string                          // 例: missing_translation_api_key, missing_similarity_ai_key
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
