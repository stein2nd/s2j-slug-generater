# S2J Slug Generater - 類似度

← [索引](./specs.md)

類似度は、[S2J Similarity Service](https://github.com/stein2nd/s2j-similarity-service) のコサイン類似度です。

* FOP: 比較の I/O は、Similarity Adapter (外枠)、パーセント変換・閾値判定は、純関数 (内側)。
* 管理画面の案内・モデル一覧は、翻訳プロバイダと同じく **記述子レジストリ** に持つ。URL の直書きはしない。

## レジストリ

翻訳の [05-providers.md](./05-providers.md) と同じ持ち方です。クラス階層は置きません。

```text
similarityAiProviders: Map<SimilarityAiProviderId, SimilarityAiProvider>

lookupSimilarityAiProvider(id: SimilarityAiProviderId)
  : Result<SimilarityAiProvider, UnknownProvider>
```

## 記述子のフィールド

案内文は、記述子から組み立てます (表示用の組立。i18n は、境界で `__()`)。

| フィールド | 層 | 用途 |
| --- | --- | --- |
| `id` | データ | `PluginConfig.similarityAiProviderId` |
| `signupUrl` | データ | アカウント未所持者向けの登録入口 |
| `billingUrl` | データ | クレジット / 課金 |
| `keysUrl` | データ | API キー発行 |
| `models` | データ | モデルドロップダウン。先頭を default とする |
| `compare` | 外枠 Adapter | `SimilarityRequest → Effect<Result<SimilarityRatio, E>>` |

```text
formatSimilarityApiKeyHelp(provider: SimilarityAiProvider): string
// sprintf(
//   __('Create an account on <a target="_blank" href="%1$s">the signup page</a>, add credits on <a target="_blank" href="%2$s">the billing page</a>, then <a target="_blank" href="%3$s">obtain an API key</a>. This key is separate from the translation API key.'),
//   provider.signupUrl,
//   provider.billingUrl,
//   provider.keysUrl
// )
```

設定画面に出すのは、この短い案内だけです。プラットフォーム固有の画面操作 (Owned by、Permissions、Monthly reload など) は書きません。UI がすぐ変わるため、docs の検証メモに留めます。

## 組込みプロバイダ

現行の組込みは `openai` のみです。追加時は、記述子を1件足します。

### OpenAI (`openai`)

* ChatGPT アプリのログインとは別製品です。埋め込み API は [OpenAI API Platform](https://platform.openai.com/) のキーとクレジットが必要です。
* キー発行だけでは不足することがあります。残高0だと比較は失敗します (クレジット不足。ライブラリが `rate limited` と分類することがある)。

| 項目 | 値 |
| --- | --- |
| signupUrl | `https://platform.openai.com/signup` |
| billingUrl | `https://platform.openai.com/settings/organization/billing` |
| keysUrl | `https://platform.openai.com/api-keys` |
| models (先頭が default) | `text-embedding-3-small`、`text-embedding-3-large`、`text-embedding-ada-002` |

## 外枠: Similarity Adapter

```text
compareSimilarity(req: SimilarityRequest)
  -> Effect<Result<SimilarityRatio, SimilarityError>>
```

呼び出し側は、`lookupSimilarityAiProvider(config.similarityAiProviderId)` で取り出した `compare` を使います (`translate` と同じ)。

実装イメージ (PHP: `includes/Similarity/`):

* Composer: [`s2j/similarity-service`](https://packagist.org/packages/s2j/similarity-service) (`^2.0`)
* `openai` の `compare`: `SimilarityService::similarity($a, $b, $model)` (現行 API に合わせる)
* 戻り値 (0.0〜1.0) を `SimilarityRatio` として返す

| ドメイン | 入力 | 現行 Adapter |
| --- | --- | --- |
| `baseText` | 元タイトル | 使用 |
| `targetText` | 逆翻訳テキスト | 使用 |
| `apiKey` | `PluginConfig.similarityAiApiKey` | 使用 (`openai` では `OpenAIEmbeddingStrategy`) |
| `model` | `PluginConfig.similarityAiModel` | 使用。選択中プロバイダの `models` に含まれること |
| `language` | `PluginConfig.sourceLanguage` | 設定保持のみ (未渡し) |
| `locale` | `PluginConfig.locale` | 設定保持のみ (未渡し) |

`similarityAiApiKey` が空なら `Err` (`code: missing_similarity_ai_key`) となります。メッセージは Settings → S2J Slug Generater に誘導し、翻訳キーとは別である旨を含みます。

旧オブジェクト指向プログラミング Strategy クラス階層は、必須としません。差し替えは、**関数の注入** (または Adapter の差し替え) で足ります。

## 内側: 純関数

* 閾値の保持・比較は、常に **ratio (0.0〜1.0)**
* UI / REST は、**percent (0.0〜100.0)**

* Clean Coding: これらの関数に HTTP や Option を混ぜない。

```text
toPercent(ratio: SimilarityRatio): SimilarityPercent
  = ratio * 100

formatPercentLabel(percent: SimilarityPercent): string
  = 小数第2位までの "n.99" 形

passesThreshold(ratio: SimilarityRatio, threshold: Threshold): bool
  = ratio >= threshold
```

## 設定 UI 用の選択肢データ

モデル一覧は、選択中の `SimilarityAiProvider.models` です。翻訳の `provider.languages` に相当します。

ロケールはプロバイダ非依存です。例: `ja_JP` (default)、`en_US`、`fr_FR`。

## 設定との関係

* プロバイダで変わるもの: 案内リンク、モデル一覧、`compare` 実装。
* 共通 (`PluginConfig`): 類似度 AI の選択 (`similarityAiProviderId`)、API キー、モデル、ロケール、類似度閾値。
