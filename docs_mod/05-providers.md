# S2J Slug Generater - 翻訳プロバイダ

← [索引](./specs.md)

旧仕様の Abstract Factory を、**データ + Adapter 関数のレジストリ** に置き換えます (FOP の外枠)。
クラス階層を増やさず、記述子を1件追加して拡張します。

* Clean Coding: プロバイダ固有の HTTP/JSON は `translate` の中だけに閉じ、パイプラインからは文字列の `Result` だけが見えるようにする。

## レジストリ

```text
providers: Map<ProviderId, TranslationProvider>

lookupProvider(id: ProviderId): Result<TranslationProvider, UnknownProvider>
```

## 記述子のフィールド

案内文は、記述子から組み立てます (表示用の組立。i18n は、境界で `__()`)。

| フィールド | 層 | 用途 |
| --- | --- | --- |
| `id` | データ | `PluginConfig.providerId` |
| `planUrl` / `freeKeyUrl` | データ | 管理画面の案内 |
| `endpoint` | データ | デフォルトホストのヒント (DeepL は runtime で plan により解決) |
| `languages` | データ | 言語ドロップダウン |
| `translate` | 外枠 Adapter | `TranslateRequest → Effect<Result<string, E>>` |

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
| endpoint (記述子デフォルト) | `https://api.deepl.com/v2/translate` |
| languages | DeepL API 仕様の言語コード (default 選択: `ja`) |

#### ホスト解決 (`PluginConfig.deeplApiPlan`)

* キー末尾 `:fx` だけで Free と断定しない (Individual 等で `:fx` でも Pro ホスト向けのキーがある)
* DeepL Translator (Individual 等) と DeepL API Translate は別製品。Translate スコープのないキーは拒否される

| plan | ホスト |
| --- | --- |
| `pro` | `https://api.deepl.com/v2/translate` |
| `free` | `https://api-free.deepl.com/v2/translate` |
| `auto` (default) | まず `api.deepl.com`。認証失敗時に `api-free` へ1回リトライ |

#### 実装詳細 (外枠)

* 送信: cURL 直たたき、JSON body、`Authorization: DeepL-Auth-Key …`
* ペーストノイズ (`DeepL-Auth-Key` 接頭辞・引用符) は正規化して除去
* `401`/`403` 時は `/v2/usage` を両ホストで診断し、Translate 権限不足などをメッセージ化

### Google Translate (`google`)

`deeplApiPlan` は無視します。

| 項目 | 値 |
| --- | --- |
| planUrl | `https://cloud.google.com/translate/pricing?hl=ja` |
| freeKeyUrl | `https://cloud.google.com/translate/docs/setup?hl=ja` |
| endpoint | `https://translation.googleapis.com/language/translate/v2` |
| languages | Google Translate API 仕様の言語コード (default 選択: `ja`) |

## Adapter 契約

GoF デザインパターンでの Adapter の役割を果たすが、実装はクラス必須ではありません。読みやすければ、関数モジュールでも良いです。

```text
translate(req: TranslateRequest) -> Effect<Result<TranslatedText, TranslateError>>

TranslateError =
  | MissingTranslationApiKey   // code: missing_translation_api_key
  | InvalidApiKey
  | HttpError({ status, body })
  | ParseError
  | ProviderSpecificError(message)
```

## 設定との関係

* プロバイダで変わるもの: 案内リンク、エンドポイント、言語一覧、`translate` 実装。
* 共通 (`PluginConfig`): 翻訳 API キー、翻訳元言語、類似度閾値、類似度用 AI (`similarityAiProviderId` / `similarityAiApiKey` / `similarityAiModel`・別レジストリ)、DeepL 向け `deeplApiPlan`。

類似度 AI の記述子・案内の組立は、[06-similarity.md](./06-similarity.md) を参照してください。
