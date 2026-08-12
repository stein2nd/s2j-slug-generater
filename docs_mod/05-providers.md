# S2J Slug Generater - 翻訳プロバイダ

← [索引](./specs.md)

旧 Abstract Factory を、**データ + Adapter 関数のレジストリ** に置き換えます (FOP の外枠)。  
クラス階層を増やさず、記述子を1件追加して拡張します。

Clean Coding: プロバイダ固有の HTTP/JSON は `translate` の中だけに閉じ、パイプラインからは文字列の `Result` だけが見えるようにします。

## レジストリ

```text
providers: Map<ProviderId, TranslationProvider>

lookupProvider(id: ProviderId): Result<TranslationProvider, UnknownProvider>
```

## 記述子のフィールド

| フィールド | 層 | 用途 |
| --- | --- | --- |
| `id` | データ | `PluginConfig.providerId` |
| `planUrl` / `freeKeyUrl` | データ | 管理画面の案内 |
| `endpoint` | データ | 無料枠エンドポイント |
| `languages` | データ | 言語ドロップダウン |
| `translate` | 外枠 Adapter | `TranslateRequest → Effect<Result<string, E>>` |

案内文は、記述子から組み立てます (表示用の組立。i18n は、境界で `__()`)。

```text
formatApiKeyHelp(provider: TranslationProvider): string
// sprintf(
//   __('Go to <a target="_blank" href="%1$s">the API plan selection page</a> and <a target="_blank" href="%2$s">obtain a free API key</a>.'),
//   provider.planUrl,
//   provider.freeKeyUrl
// )
```

## 組込みプロバイダ

### DeepL (`deepl`)

| 項目 | 値 |
| --- | --- |
| planUrl | `https://www.deepl.com/pro-api#api-pricing` |
| freeKeyUrl | `https://www.deepl.com/ja/pro#developer` |
| endpoint | `https://api-free.deepl.com/v2/translate` |
| languages | DeepL API 仕様の言語コード (default 選択: `ja`) |

### Google Translate (`google`)

| 項目 | 値 |
| --- | --- |
| planUrl | `https://cloud.google.com/translate/pricing?hl=ja` |
| freeKeyUrl | `https://cloud.google.com/translate/docs/setup?hl=ja` |
| endpoint | `https://translation.googleapis.com/language/translate/v2` |
| languages | Google Translate API 仕様の言語コード (default 選択: `ja`) |

## Adapter 契約

```text
translate(req: TranslateRequest) -> Effect<Result<TranslatedText, TranslateError>>

TranslateError =
  | InvalidApiKey
  | HttpError({ status, body })
  | ParseError
  | ProviderSpecificError(message)
```

GoF デザインパターンでの Adapter の役割を果たすが、実装はクラス必須ではありません。読みやすければ、関数モジュールでも良いです。

## 設定との関係

* プロバイダで変わるもの: 案内リンク、エンドポイント、言語一覧、`translate` 実装。  
* 共通 (`PluginConfig`): 翻訳 API キー、翻訳元言語、類似度閾値、類似度用 AI (`similarityAiApiKey` / `similarityAiModel`・別 Adapter)。

次: [06-similarity.md](./06-similarity.md)
