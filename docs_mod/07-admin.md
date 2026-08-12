# S2J Slug Generater - 管理画面

← [索引](./specs.md)

管理画面は、**Config の読取 / 編集 / 永続化** を担う外枠 (薄い Facade + Option Adapter) です。  
ドメインのビジネスロジック (翻訳 / 類似度 / スラッグ正規化) は、行いません。

## 流れ (FOP)

```text
loadConfig()                                            // 外枠
  → toFormState(config)                                 // 純: Config → FormState
  → ユーザー編集                                         // FormState を更新 (UI)
  → validateForm(formState) → Result<PluginConfig, E>   // 内側 pure
  → saveConfig(config)                                  // 外枠
```

## フォーム項目

| UI | `PluginConfig` | 備考 |
| --- | --- | --- |
| 翻訳 API 選択 | `providerId` | 言語一覧・案内リンクを切替 |
| API キー | `translationApiKey` | 選択プロバイダの `freeKeyUrl` 案内 |
| 翻訳元言語 | `sourceLanguage` | `provider.languages`。default `ja` |
| 類似度用 AI API キー | `similarityAiApiKey` | 類似度 (埋め込み) に使う AI サービスのキー。翻訳の `translationApiKey` とは別。案内例: `https://platform.openai.com/api-keys` |
| 類似度用 AI モデル名 | `similarityAiModel` | 類似度 (埋め込み) に使う AI モデル。default 例: `text-embedding-3-small` |
| ロケール | `locale` | default は `ja_JP` |
| 類似度閾値スライダー | `similarityThreshold` | 0.0〜1.0 / step: 0.1 / default: 0.8。表示は % |

## プロバイダ連動

```text
deriveProviderUi(providerId) =          // 純: 派生 UI 状態
  let provider = lookupProvider(providerId)
  { languages: provider.languages, helpHtml: formatApiKeyHelp(provider) }

applyProviderUi(uiState)                // 外枠: DOM / React へ反映
```

旧 SPEC の「jQuery で DOM 操作」は、実装詳細です。仕様上は、上記の純派生 + 適用に分けます。

## 永続化

* Option キーは、`s2j_slug_generater_*` などプラグイン・スラッグにそろえる
* 候補生成は、`PluginConfig` を受け取る (呼び出し側で `loadConfig`)。純関数内で `get_option` を散在させない

## やってはいけないこと

* 設定保存時に、翻訳 / 類似度 API を必須コールしない (検証 UI は将来の別 effect)
* スラッグ正規化や類似度計算を、設定画面に持ち込まない (Clean Coding: 責務を混ぜない)

次: [08-editor.md](./08-editor.md)
