# S2J Slug Generater - 管理画面

← [索引](./specs.md)

管理画面は、**Config の読取 / 編集 / 永続化** を担う外枠 (薄い Facade + Option Adapter) です。
ドメインのビジネスロジック (翻訳 / 類似度 / スラッグ正規化) は、行いません。

実装の正本は、**PHP Settings API** (`includes/Admin/SettingsPage.php`) です。`src/admin` は、スタイル等の補助にとどめます。

## 流れ (FOP)

```text
loadConfig()                                            // 外枠
  → toFormState(config)                                 // 純: Config → FormState
  → ユーザー編集                                         // FormState を更新 (UI)
  → validateForm(formState) → Result<PluginConfig, E>   // 内側 pure / Settings sanitize
  → saveConfig(config)                                  // 外枠
```

## フォーム項目

| UI | `PluginConfig` | 備考 |
| --- | --- | --- |
| 翻訳 API 選択 | `providerId` | 言語一覧・案内リンクを切替 |
| Translation API Key | `translationApiKey` | `type="text"` (`autocomplete="off"`)。password 欄は使わない (パスワードマネージャー誤送信対策) |
| DeepL API Plan | `deeplApiPlan` | `auto` / `free` / `pro`。DeepL 選択時に意味を持つ |
| 翻訳元言語 | `sourceLanguage` | `provider.languages`。default `ja` |
| 類似度 AI 選択 | `similarityAiProviderId` | モデル一覧・案内リンクを切替。default `openai` |
| Similarity AI API Key | `similarityAiApiKey` | 埋め込み用。翻訳キーとは別。`type="text"`。案内は `formatSimilarityApiKeyHelp` |
| Similarity AI Model | `similarityAiModel` | `similarityAiProvider.models`。default は先頭 |
| Locale | `locale` | default `ja_JP`。現行 Similarity 呼び出しには未使用 (将来用として保持) |
| 類似度閾値スライダー | `similarityThreshold` | 0.0〜1.0 / step: 0.1 / default: 0.8。表示は % |

## プロバイダ連動

旧 SPEC の「jQuery で DOM 操作」は、実装詳細です。仕様上は、上記の純派生 + 適用に分けます。翻訳と類似度で同じ形です。

```text
deriveProviderUi(providerId) =          // 純: 派生 UI 状態
  let provider = lookupProvider(providerId)
  { languages: provider.languages, helpHtml: formatApiKeyHelp(provider) }

applyProviderUi(uiState)                // 外枠: DOM に反映

deriveSimilarityProviderUi(similarityAiProviderId) =
  let provider = lookupSimilarityAiProvider(similarityAiProviderId)
  { models: provider.models, helpHtml: formatSimilarityApiKeyHelp(provider) }

applySimilarityProviderUi(uiState)      // 外枠: DOM に反映
```

類似度の案内は、アカウント作成・課金・キー発行へのリンクに留めます。プラットフォーム固有の画面操作は書きません。詳細は、[06-similarity.md](./06-similarity.md) を参照してください。

## 永続化

* Option キーは、[03-domain-model.md](./03-domain-model.md) の表に固定する
* 候補生成は、`PluginConfig` を受け取る (呼び出し側で `loadConfig`)。純関数内で `get_option` を散在させない
* 既存閾値が percent スケール (`> 1.0`) の場合は、起動時に一度だけ ratio に自動変換する

## やってはいけないこと

* 設定保存時に、翻訳 / 類似度 API を必須コールしない (検証 UI は、将来の別 effect)
* スラッグ正規化や類似度計算を、設定画面に持ち込まない (Clean Coding: 責務を混ぜない)
* 類似度キー案内に OpenAI 等の URL を直書きしない。記述子から組み立てる
* 設定画面にプラットフォーム固有の登録手順 (Monthly reload、権限トグル等) を置かない
